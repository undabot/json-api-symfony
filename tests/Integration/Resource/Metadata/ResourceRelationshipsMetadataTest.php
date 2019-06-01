<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\RelationshipMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToMany;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToOne;

class ResourceRelationshipsMetadataTest extends TestCase
{
    /** @var ResourceMetadataFactory */
    private $metadataFactory;

    protected function setUp()
    {
        parent::setUp();
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $this->metadataFactory = new ResourceMetadataFactory($annotationReader);
    }

    private function getResource()
    {
        return new class
        {
            /**
             * @var array
             * @JsonApi\ToMany(name="tags", type="tag")
             */
            public $tagIds;

            /**
             * @var string|null
             * @JsonApi\ToOne(name="owner", type="person")
             */
            public $ownerId;

            /**
             * @JsonApi\ToOne(name="emptyOne", type="emptyOneType")
             */
            public $emptyToOne;

            /**
             * @JsonApi\ToMany(name="emptyMany", type="emptyManyType")
             */
            public $emptyToMany;

            public $notARelationship;
        };
    }

    public function testResourceMetadataContainsAllAnnotatedRelationships()
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $this->assertInstanceOf(ResourceMetadata::class, $metadata);

        $this->assertCount(4, $metadata->getRelationshipsMetadata());
        $this->assertContainsOnlyInstancesOf(RelationshipMetadata::class, $metadata->getRelationshipsMetadata());

        $this->assertNotNull($metadata->getRelationshipMetadata('tags'));
        $this->assertNotNull($metadata->getRelationshipMetadata('owner'));
        $this->assertNotNull($metadata->getRelationshipMetadata('emptyOne'));
        $this->assertNotNull($metadata->getRelationshipMetadata('emptyMany'));
        $this->assertNull($metadata->getRelationshipMetadata('notARelationship'));
    }

    public function testResourceMetadataContainsValidTagsRelationshipMetadata()
    {
        $resource = $this->getResource();
        $metadata = $this->metadataFactory->getResourceMetadata($resource);
        $this->assertInstanceOf(ResourceMetadata::class, $metadata);

        $tagsMetadata = $metadata->getRelationshipMetadata('tags');

        $this->assertSame('tags', $tagsMetadata->getName());
        $this->assertSame('tag', $tagsMetadata->getRelatedResourceType());

        $this->assertCount(2, $tagsMetadata->getConstraints());
        $this->assertContainsOnlyInstancesOf(Constraint::class, $tagsMetadata->getConstraints());

        $expectationMap = [
            ResourceType::class => 0,
            ToMany::class => 0,
        ];

        foreach ($tagsMetadata->getConstraints() as $constraint) {
            $expectationMap[get_class($constraint)]++;
            if ($constraint instanceof ResourceType) {
                $this->assertSame('tag', $constraint->getType());
            }
        }

        foreach ($expectationMap as $constraintClass => $expectationCount) {
            $this->assertSame(1, $expectationCount, $constraintClass);
        }
    }

    public function testResourceMetadataContainsValidOwnerRelationshipMetadata()
    {
        $resource = $this->getResource();
        $metadata = $this->metadataFactory->getResourceMetadata($resource);
        $this->assertInstanceOf(ResourceMetadata::class, $metadata);

        $tagsMetadata = $metadata->getRelationshipMetadata('owner');

        $this->assertSame('owner', $tagsMetadata->getName());
        $this->assertSame('person', $tagsMetadata->getRelatedResourceType());

        $this->assertCount(2, $tagsMetadata->getConstraints());
        $this->assertContainsOnlyInstancesOf(Constraint::class, $tagsMetadata->getConstraints());

        $expectationMap = [
            ResourceType::class => 0,
            ToOne::class => 0,
        ];

        foreach ($tagsMetadata->getConstraints() as $constraint) {
            $expectationMap[get_class($constraint)]++;
            if ($constraint instanceof ResourceType) {
                $this->assertSame('person', $constraint->getType());
            }
        }

        foreach ($expectationMap as $constraintClass => $expectationCount) {
            $this->assertSame(1, $expectationCount, $constraintClass);
        }
    }

    public function testMetadataFactoryThrowsAnExceptionWhenSinglePropertyHasMultipleRelationshipAnnotations()
    {
        $resource = new class
        {
            /**
             * @JsonApi\ToMany(name="tag")
             * @JsonApi\ToOne(name="tag2")
             */
            public $tagId;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->metadataFactory->getResourceMetadata($resource);
    }

    public function testRelatioinshipNameCanBeOveridden()
    {
        $resource = new class
        {
            /** @JsonApi\ToOne(type="resource") */
            public $defaultName;

            /** @JsonApi\ToOne(name="overridenName", type="resource") */
            public $defaultName2;
        };

        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $this->assertInstanceOf(ResourceMetadata::class, $metadata);
        $this->assertCount(2, $metadata->getRelationshipsMetadata());

        $this->assertInstanceOf(RelationshipMetadata::class, $metadata->getRelationshipMetadata('defaultName'));

        $this->assertNull($metadata->getRelationshipMetadata('defaultName2'));
        $this->assertInstanceOf(RelationshipMetadata::class, $metadata->getRelationshipMetadata('overridenName'));
    }

    public function testRelationshipMetadataIsEmptyWhenNoRelationshipsAnnotated()
    {
        $resource = new class
        {
        };

        $metadata = $this->metadataFactory->getResourceMetadata($resource);
        $this->assertTrue($metadata->getRelationshipsMetadata()->isEmpty());
    }
}
