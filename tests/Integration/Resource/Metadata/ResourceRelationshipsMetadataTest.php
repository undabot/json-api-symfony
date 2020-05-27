<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\RelationshipMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToMany;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToOne;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class ResourceRelationshipsMetadataTest extends TestCase
{
    /** @var ResourceMetadataFactory */
    private $metadataFactory;

    protected function setUp(): void
    {
        parent::setUp();
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $this->metadataFactory = new ResourceMetadataFactory($annotationReader);
    }

    public function testResourceMetadataContainsAllAnnotatedRelationships(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        static::assertInstanceOf(ResourceMetadata::class, $metadata);

        static::assertCount(4, $metadata->getRelationshipsMetadata());
        static::assertContainsOnlyInstancesOf(RelationshipMetadata::class, $metadata->getRelationshipsMetadata());

        static::assertNotNull($metadata->getRelationshipMetadata('tags'));
        static::assertNotNull($metadata->getRelationshipMetadata('owner'));
        static::assertNotNull($metadata->getRelationshipMetadata('emptyOne'));
        static::assertNotNull($metadata->getRelationshipMetadata('emptyMany'));
        static::assertNull($metadata->getRelationshipMetadata('notARelationship'));
    }

    public function testResourceMetadataContainsValidTagsRelationshipMetadata(): void
    {
        $resource = $this->getResource();
        $metadata = $this->metadataFactory->getInstanceMetadata($resource);
        static::assertInstanceOf(ResourceMetadata::class, $metadata);

        $tagsMetadata = $metadata->getRelationshipMetadata('tags');

        static::assertSame('tags', $tagsMetadata->getName());
        static::assertSame('tag', $tagsMetadata->getRelatedResourceType());

        static::assertCount(2, $tagsMetadata->getConstraints());
        static::assertContainsOnlyInstancesOf(Constraint::class, $tagsMetadata->getConstraints());

        $expectationMap = [
            ResourceType::class => 0,
            ToMany::class => 0,
        ];

        foreach ($tagsMetadata->getConstraints() as $constraint) {
            ++$expectationMap[\get_class($constraint)];
            if ($constraint instanceof ResourceType) {
                static::assertSame('tag', $constraint->getType());
            }
        }

        foreach ($expectationMap as $constraintClass => $expectationCount) {
            static::assertSame(1, $expectationCount, $constraintClass);
        }
    }

    public function testResourceMetadataContainsValidOwnerRelationshipMetadata(): void
    {
        $resource = $this->getResource();
        $metadata = $this->metadataFactory->getInstanceMetadata($resource);
        static::assertInstanceOf(ResourceMetadata::class, $metadata);

        $tagsMetadata = $metadata->getRelationshipMetadata('owner');

        static::assertSame('owner', $tagsMetadata->getName());
        static::assertSame('person', $tagsMetadata->getRelatedResourceType());

        static::assertCount(2, $tagsMetadata->getConstraints());
        static::assertContainsOnlyInstancesOf(Constraint::class, $tagsMetadata->getConstraints());

        $expectationMap = [
            ResourceType::class => 0,
            ToOne::class => 0,
        ];

        foreach ($tagsMetadata->getConstraints() as $constraint) {
            ++$expectationMap[\get_class($constraint)];
            if ($constraint instanceof ResourceType) {
                static::assertSame('person', $constraint->getType());
            }
        }

        foreach ($expectationMap as $constraintClass => $expectationCount) {
            static::assertSame(1, $expectationCount, $constraintClass);
        }
    }

    public function testMetadataFactoryThrowsAnExceptionWhenSinglePropertyHasMultipleRelationshipAnnotations(): void
    {
        /**
         * @ResourceType(type="resource")
         */
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\ToMany(name="tag")
             * @JsonApi\ToOne(name="tag2")
             */
            public $tagId;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testRelatioinshipNameCanBeOveridden(): void
    {
        /**
         * @ResourceType(type="resource")
         */
        $resource = new class() implements ApiModel {
            /** @JsonApi\ToOne(type="resource") */
            public $defaultName;

            /** @JsonApi\ToOne(name="overridenName", type="resource") */
            public $defaultName2;
        };

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        static::assertInstanceOf(ResourceMetadata::class, $metadata);
        static::assertCount(2, $metadata->getRelationshipsMetadata());

        static::assertInstanceOf(RelationshipMetadata::class, $metadata->getRelationshipMetadata('defaultName'));

        static::assertNull($metadata->getRelationshipMetadata('defaultName2'));
        static::assertInstanceOf(RelationshipMetadata::class, $metadata->getRelationshipMetadata('overridenName'));
    }

    public function testRelationshipMetadataIsEmptyWhenNoRelationshipsAnnotated(): void
    {
        /**
         * @ResourceType(type="resource")
         */
        $resource = new class() implements ApiModel {
        };

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);
        static::assertTrue($metadata->getRelationshipsMetadata()->isEmpty());
    }

    private function getResource()
    {
        /**
         * @ResourceType(type="resource")
         */
        return new class() implements ApiModel {
            /**
             * @var array
             * @JsonApi\ToMany(name="tags", type="tag")
             */
            public $tagIds;

            /**
             * @var null|string
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
}
