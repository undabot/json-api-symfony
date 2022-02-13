<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Resource\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\Data\ToManyRelationshipDataInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\Data\ToOneRelationshipDataInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceIdentifier;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Model\Resource\FlatResource;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidator;

/**
 * @ResourceType(type="resource")
 */
class ResourceDto implements ApiModel
{
    /**
     * @Assert\NotBlank
     * @var string
     */
    public $id;

    /**
     * @var string
     * @JsonApi\Attribute
     * @Assert\NotBlank
     */
    public $title;

    /**
     * @var string
     * @JsonApi\Attribute
     * @Assert\NotBlank
     */
    public $date;

    /**
     * @var string
     * @JsonApi\Attribute
     * @Assert\NotBlank
     */
    public $summary;

    /**
     * @var null|string
     * @JsonApi\ToOne(type="people")
     */
    public $author;

    /**
     * @var string[]
     * @JsonApi\ToMany(type="tags")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    public $tags = [];

    /**
     * @var string[]
     * @JsonApi\ToMany(type="comments")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    public $comments = [];
}

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class ResourceFactoryTest extends TestCase
{
    private ResourceFactory $resourceFactory;
    private bool $shouldValidateReadModel = false;
    private ValidatorInterface $validatorMock;

    protected function setUp(): void
    {
        parent::setUp();
        $annotationReader = new AnnotationReader();
        $metadataFactory = new ResourceMetadataFactory($annotationReader);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $resourceValidator = new ResourceValidator($metadataFactory, $this->validatorMock);
        $this->resourceFactory = new ResourceFactory(
            $metadataFactory,
            $this->shouldValidateReadModel,
            $resourceValidator
        );
    }

    public function testResourceFactoryCreatesValidResourceWithoutAttributesOrRelationshipsValues(): void
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';

        $resource = $this->resourceFactory->make($resourceDto);
        static::assertInstanceOf(ResourceInterface::class, $resource);
        static::assertSame('1', $resource->getId());
        static::assertSame('resource', $resource->getType());
        static::assertCount(3, $resource->getAttributes());
        static::assertCount(3, $resource->getRelationships());
    }

    public function testResourceFactoryCreatesValidResourceWithValues(): void
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
        static::assertInstanceOf(ResourceInterface::class, $resource);
        static::assertSame('1', $resource->getId());
        static::assertSame('resource', $resource->getType());
        static::assertCount(3, $resource->getAttributes());
        static::assertCount(3, $resource->getRelationships());

        $flatResource = new FlatResource($resource);
        static::assertSame('Resource title', $flatResource->getAttributes()['title']);
        static::assertSame('Resource summary', $flatResource->getAttributes()['summary']);
        static::assertSame('2018-01-01', $flatResource->getAttributes()['date']);
        static::assertSame(['t1', 't2', 't3'], $flatResource->getRelationships()['tags']);
        static::assertSame(['c1', 'c2', 'c3'], $flatResource->getRelationships()['comments']);
        static::assertSame('a1', $flatResource->getRelationships()['author']);
    }

    public function testResourceFactoryCreatesValidTagsRelationship(): void
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->tags = ['t1', 't2', 't3'];

        $resource = $this->resourceFactory->make($resourceDto);
        static::assertInstanceOf(ResourceInterface::class, $resource);
        static::assertSame('1', $resource->getId());
        static::assertSame('resource', $resource->getType());

        $tagsRelationship = $resource->getRelationships()->getRelationshipByName('tags');
        static::assertInstanceOf(RelationshipInterface::class, $tagsRelationship);
        static::assertInstanceOf(ToManyRelationshipDataInterface::class, $tagsRelationship->getData());

        static::assertEquals(
            new ResourceIdentifier('t1', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[0]
        );

        static::assertEquals(
            new ResourceIdentifier('t2', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[1]
        );

        static::assertEquals(
            new ResourceIdentifier('t3', 'tags'),
            $tagsRelationship->getData()->getData()->getResourceIdentifiers()[2]
        );
    }

    public function testResourceFactoryCreatesValidEmptyTagsRelationshipFromEmptyArray(): void
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->tags = [];

        $resource = $this->resourceFactory->make($resourceDto);
        static::assertInstanceOf(ResourceInterface::class, $resource);
        static::assertSame('1', $resource->getId());
        static::assertSame('resource', $resource->getType());

        $tagsRelationship = $resource->getRelationships()->getRelationshipByName('tags');
        static::assertInstanceOf(RelationshipInterface::class, $tagsRelationship);
        static::assertInstanceOf(ToManyRelationshipDataInterface::class, $tagsRelationship->getData());
        static::assertTrue($tagsRelationship->getData()->isEmpty());
    }

    public function testResourceFactoryCreatesValidAuthorRelationship(): void
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->author = 'a1';

        $resource = $this->resourceFactory->make($resourceDto);
        static::assertInstanceOf(ResourceInterface::class, $resource);
        static::assertSame('1', $resource->getId());
        static::assertSame('resource', $resource->getType());

        $authorRelationship = $resource->getRelationships()->getRelationshipByName('author');
        static::assertInstanceOf(RelationshipInterface::class, $authorRelationship);
        static::assertInstanceOf(ToOneRelationshipDataInterface::class, $authorRelationship->getData());

        static::assertEquals(
            new ResourceIdentifier('a1', 'people'),
            $authorRelationship->getData()->getData()
        );
    }

    public function testMakeWillCreateValidResourceGivenWriteModelShouldNotBeValidatedAndResourceHasValues(): void
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->title = 'Resource title';
        $resourceDto->summary = 'Resource summary';
        $resourceDto->date = '2018-01-01';
        $resourceDto->tags = ['t1', 't2', 't3'];
        $resourceDto->comments = ['c1', 'c2', 'c3'];
        $resourceDto->author = 'a1';

        $this->shouldValidateReadModel = false;
        $resource = $this->resourceFactory->make($resourceDto);
        static::assertInstanceOf(ResourceInterface::class, $resource);
        static::assertSame('1', $resource->getId());
        static::assertSame('resource', $resource->getType());
        static::assertCount(3, $resource->getAttributes());
        static::assertCount(3, $resource->getRelationships());

        $flatResource = new FlatResource($resource);
        static::assertSame('Resource title', $flatResource->getAttributes()['title']);
        static::assertSame('Resource summary', $flatResource->getAttributes()['summary']);
        static::assertSame('2018-01-01', $flatResource->getAttributes()['date']);
        static::assertSame(['t1', 't2', 't3'], $flatResource->getRelationships()['tags']);
        static::assertSame(['c1', 'c2', 'c3'], $flatResource->getRelationships()['comments']);
        static::assertSame('a1', $flatResource->getRelationships()['author']);
    }

    public function testMakeWillCreateValidResourceGivenWriteModelShouldBeValidatedAndResourceHasValuesAndConstraints(): void
    {
        $resourceDto = new ResourceDto();
        $resourceDto->id = '1';
        $resourceDto->title = 'Resource title';
        $resourceDto->summary = 'Resource summary';
        $resourceDto->date = '2018-01-01';
        $resourceDto->tags = ['t1', 't2', 't3'];
        $resourceDto->comments = ['c1', 'c2', 'c3'];
        $resourceDto->author = 'a1';

        $this->shouldValidateReadModel = true;
        $resource = $this->resourceFactory->make($resourceDto);
        static::assertInstanceOf(ResourceInterface::class, $resource);
        static::assertSame('1', $resource->getId());
        static::assertSame('resource', $resource->getType());
        static::assertCount(3, $resource->getAttributes());
        static::assertCount(3, $resource->getRelationships());

        $flatResource = new FlatResource($resource);
        static::assertSame('Resource title', $flatResource->getAttributes()['title']);
        static::assertSame('Resource summary', $flatResource->getAttributes()['summary']);
        static::assertSame('2018-01-01', $flatResource->getAttributes()['date']);
        static::assertSame(['t1', 't2', 't3'], $flatResource->getRelationships()['tags']);
        static::assertSame(['c1', 'c2', 'c3'], $flatResource->getRelationships()['comments']);
        static::assertSame('a1', $flatResource->getRelationships()['author']);
    }
}
