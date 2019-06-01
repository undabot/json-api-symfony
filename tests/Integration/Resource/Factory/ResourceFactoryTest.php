<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToManyRelationshipDataInterface;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToOneRelationshipDataInterface;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Model\Resource\ResourceIdentifier;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Model\Resource\FlatResource;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/**
 * @ResourceType(type="resource")
 */
class ResourceDto
{
    /** @var string */
    public $id;

    /**
     * @var string
     * @JsonApi\Attribute()
     */
    public $title;

    /**
     * @var string
     * @JsonApi\Attribute()
     */
    public $date;

    /**
     * @var string
     * @JsonApi\Attribute()
     */
    public $summary;

    /**
     * @var string|null
     * @JsonApi\ToOne(type="people")
     */
    public $author;

    /**
     * @var string[]
     * @JsonApi\ToMany(type="tags")
     */
    public $tags = [];

    /**
     * @var string[]
     * @JsonApi\ToMany(type="comments")
     */
    public $comments = [];
}

class ResourceFactoryTest extends TestCase
{
    /** @var ResourceFactory */
    private $resourceFactory;

    protected function setUp()
    {
        parent::setUp();
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $metadataFactory = new ResourceMetadataFactory($annotationReader);
        $this->resourceFactory = new ResourceFactory($metadataFactory);
    }

    public function testResourceFactoryCreatesValidResourceWithoutAttributesOrRelationshipsValues()
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertSame('1', $resource->getId());
        $this->assertSame('resource', $resource->getType());
        $this->assertCount(3, $resource->getAttributes());
        $this->assertCount(3, $resource->getRelationships());
    }

    public function testResourceFactoryCreatesValidResourceWithValues()
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->title = 'Resource title';
        $resourceDto->summary = 'Resource summary';
        $resourceDto->date = '2018-01-01';
        $resourceDto->tags = ['t1', 't2', 't3'];
        $resourceDto->comments = ['c1', 'c2', 'c3'];
        $resourceDto->author = 'a1';

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertSame('1', $resource->getId());
        $this->assertSame('resource', $resource->getType());
        $this->assertCount(3, $resource->getAttributes());
        $this->assertCount(3, $resource->getRelationships());

        $flatResource = new FlatResource($resource);
        $this->assertSame('Resource title', $flatResource->getAttributes()['title']);
        $this->assertSame('Resource summary', $flatResource->getAttributes()['summary']);
        $this->assertSame('2018-01-01', $flatResource->getAttributes()['date']);
        $this->assertSame(['t1', 't2', 't3'], $flatResource->getRelationships()['tags']);
        $this->assertSame(['c1', 'c2', 'c3'], $flatResource->getRelationships()['comments']);
        $this->assertSame('a1', $flatResource->getRelationships()['author']);
    }

    public function testResourceFactoryCreatesValidTagsRelationship()
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->tags = ['t1', 't2', 't3'];

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertSame('1', $resource->getId());
        $this->assertSame('resource', $resource->getType());

        $tagsRelationship = $resource->getRelationships()->getRelationshipByName('tags');
        $this->assertInstanceOf(RelationshipInterface::class, $tagsRelationship);
        $this->assertInstanceOf(ToManyRelationshipDataInterface::class, $tagsRelationship->getData());

        $this->assertEquals(
            new ResourceIdentifier('t1', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[0]
        );

        $this->assertEquals(
            new ResourceIdentifier('t2', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[1]
        );

        $this->assertEquals(
            new ResourceIdentifier('t3', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[2]
        );
    }

    public function testResourceFactoryCreatesValidEmptyTagsRelationshipFromEmptyArray()
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->tags = [];

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertSame('1', $resource->getId());
        $this->assertSame('resource', $resource->getType());

        $tagsRelationship = $resource->getRelationships()->getRelationshipByName('tags');
        $this->assertInstanceOf(RelationshipInterface::class, $tagsRelationship);
        $this->assertInstanceOf(ToManyRelationshipDataInterface::class, $tagsRelationship->getData());
        $this->assertTrue($tagsRelationship->getData()->isEmpty());
    }

    public function testResourceFactoryCreatesValidAuthorRelationship()
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->author = 'a1';

        $resource = $this->resourceFactory->make($resourceDto);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertSame('1', $resource->getId());
        $this->assertSame('resource', $resource->getType());

        $authorRelationship = $resource->getRelationships()->getRelationshipByName('author');
        $this->assertInstanceOf(RelationshipInterface::class, $authorRelationship);
        $this->assertInstanceOf(ToOneRelationshipDataInterface::class, $authorRelationship->getData());

        $this->assertEquals(
            new ResourceIdentifier('a1', 'people'),
            $authorRelationship->getData()->getData()
        );
    }
}
