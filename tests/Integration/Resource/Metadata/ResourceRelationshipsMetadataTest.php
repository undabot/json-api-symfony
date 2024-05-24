<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Resource\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
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
 *
 * @coversNothing
 *
 * @small
 */
#[CoversNothing]
#[Small]
final class ResourceRelationshipsMetadataTest extends TestCase
{
    /** @var ResourceMetadataFactory */
    private $metadataFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $annotationReader = new AnnotationReader();
        $this->metadataFactory = new ResourceMetadataFactory($annotationReader);
    }

    public function testResourceMetadataContainsAllAnnotatedRelationships(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        self::assertInstanceOf(ResourceMetadata::class, $metadata);

        self::assertCount(4, $metadata->getRelationshipsMetadata());
        self::assertContainsOnlyInstancesOf(RelationshipMetadata::class, $metadata->getRelationshipsMetadata());

        self::assertNotNull($metadata->getRelationshipMetadata('tags'));
        self::assertNotNull($metadata->getRelationshipMetadata('owner'));
        self::assertNotNull($metadata->getRelationshipMetadata('emptyOne'));
        self::assertNotNull($metadata->getRelationshipMetadata('emptyMany'));
        self::assertNull($metadata->getRelationshipMetadata('notARelationship'));
    }

    public function testResourceMetadataContainsValidTagsRelationshipMetadata(): void
    {
        $resource = $this->getResource();
        $metadata = $this->metadataFactory->getInstanceMetadata($resource);
        self::assertInstanceOf(ResourceMetadata::class, $metadata);

        $tagsMetadata = $metadata->getRelationshipMetadata('tags');

        self::assertSame('tags', $tagsMetadata->getName());
        self::assertSame('tag', $tagsMetadata->getRelatedResourceType());

        self::assertCount(2, $tagsMetadata->getConstraints());
        self::assertContainsOnlyInstancesOf(Constraint::class, $tagsMetadata->getConstraints());

        $expectationMap = [
            ResourceType::class => 0,
            ToMany::class => 0,
        ];

        foreach ($tagsMetadata->getConstraints() as $constraint) {
            ++$expectationMap[\get_class($constraint)];
            if ($constraint instanceof ResourceType) {
                self::assertSame('tag', $constraint->getType());
            }
        }

        foreach ($expectationMap as $constraintClass => $expectationCount) {
            self::assertSame(1, $expectationCount, $constraintClass);
        }
    }

    public function testResourceMetadataContainsValidOwnerRelationshipMetadata(): void
    {
        $resource = $this->getResource();
        $metadata = $this->metadataFactory->getInstanceMetadata($resource);
        self::assertInstanceOf(ResourceMetadata::class, $metadata);

        $tagsMetadata = $metadata->getRelationshipMetadata('owner');

        self::assertSame('owner', $tagsMetadata->getName());
        self::assertSame('person', $tagsMetadata->getRelatedResourceType());

        self::assertCount(2, $tagsMetadata->getConstraints());
        self::assertContainsOnlyInstancesOf(Constraint::class, $tagsMetadata->getConstraints());

        $expectationMap = [
            ResourceType::class => 0,
            ToOne::class => 0,
        ];

        foreach ($tagsMetadata->getConstraints() as $constraint) {
            ++$expectationMap[\get_class($constraint)];
            if ($constraint instanceof ResourceType) {
                self::assertSame('person', $constraint->getType());
            }
        }

        foreach ($expectationMap as $constraintClass => $expectationCount) {
            self::assertSame(1, $expectationCount, $constraintClass);
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
             *
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

        self::assertInstanceOf(ResourceMetadata::class, $metadata);
        self::assertCount(2, $metadata->getRelationshipsMetadata());

        self::assertInstanceOf(RelationshipMetadata::class, $metadata->getRelationshipMetadata('defaultName'));

        self::assertNull($metadata->getRelationshipMetadata('defaultName2'));
        self::assertInstanceOf(RelationshipMetadata::class, $metadata->getRelationshipMetadata('overridenName'));
    }

    public function testRelationshipMetadataIsEmptyWhenNoRelationshipsAnnotated(): void
    {
        /**
         * @ResourceType(type="resource")
         */
        $resource = new class() implements ApiModel {};

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);
        self::assertTrue($metadata->getRelationshipsMetadata()->isEmpty());
    }

    private function getResource()
    {
        /**
         * @ResourceType(type="resource")
         */
        return new class() implements ApiModel {
            /**
             * @var array
             *
             * @JsonApi\ToMany(name="tags", type="tag")
             */
            public $tagIds;

            /**
             * @var null|string
             *
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
