<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class MetadataFactoryTest extends TestCase
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

    public function testMetadataFactoryThrowsAnExceptionWhenPropertyIsMappedAsBothAttributeAndToOneRelationship(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\Attribute
             * @JsonApi\ToOne
             */
            public $name;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Property `name` can\'t be attribute and relationship in the same time');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenPropertyIsMappedAsBothAttributeAndToManyRelationship(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\Attribute
             * @JsonApi\ToMany
             */
            public $name;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Property `name` can\'t be attribute and relationship in the same time');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenPropertyIsMappedAsBothToOneAndToManyRelationship(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\ToMany
             * @JsonApi\ToOne
             */
            public $name;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('More than 1 Relationship Annotation found for property `name`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenResourceContainsAttributeAndRelationshipWithSameNames(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\Attribute(name="test")
             */
            public $first;

            /**
             * @JsonApi\ToOne(name="test", type="test")
             */
            public $second;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource already has attribute or relationship named `test`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenResourceContainsRelationshipsWithSameNames(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\ToMany(name="test", type="test")
             */
            public $first;

            /**
             * @JsonApi\ToOne(name="test", type="test")
             */
            public $second;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource already has attribute or relationship named `test`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenResourceContainsAttributesWithSameNames(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\Attribute(name="test")
             */
            public $first;

            /**
             * @JsonApi\Attribute(name="test")
             */
            public $second;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource already has attribute or relationship named `test`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenTwoPropertyIsMappedWithTwoAttributeAnnotations(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\Attribute(name="1")
             * @JsonApi\Attribute(name="2")
             */
            public $name;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('More than 1 Attribute Annotation found for property `name`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForToOneRelationshipWithoutType(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\ToOne
             */
            public $rel;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource type for `rel` is not defined');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForToManyRelationshipWithoutType(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\ToMany
             */
            public $rel;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource type for `rel` is not defined');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForReservedIdAttribute(): void
    {
        $resource = new class() implements ApiModel {
            /** @JsonApi\Attribute */
            public $id;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource can\'t use reserved attribute or relationship name `id`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForReservedIdToOneRelationship(): void
    {
        $resource = new class() implements ApiModel {
            /** @JsonApi\ToOne(type="test") */
            public $id;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource can\'t use reserved attribute or relationship name `id`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForReservedIdToManyRelationship(): void
    {
        $resource = new class() implements ApiModel {
            /** @JsonApi\ToMany(type="test") */
            public $id;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource can\'t use reserved attribute or relationship name `id`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }
}
