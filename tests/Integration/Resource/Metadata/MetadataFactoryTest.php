<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;

class MetadataFactoryTest extends TestCase
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

    public function testMetadataFactoryThrowsAnExceptionWhenPropertyIsMappedAsBothAttributeAndToOneRelationship()
    {
        $resource = new class
        {
            /**
             * @JsonApi\Attribute()
             * @JsonApi\ToOne()
             */
            public $name;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Property `name` can\'t be attribute and relationship in the same time');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenPropertyIsMappedAsBothAttributeAndToManyRelationship()
    {
        $resource = new class
        {
            /**
             * @JsonApi\Attribute()
             * @JsonApi\ToMany()
             */
            public $name;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Property `name` can\'t be attribute and relationship in the same time');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenPropertyIsMappedAsBothToOneAndToManyRelationship()
    {
        $resource = new class
        {
            /**
             * @JsonApi\ToMany()
             * @JsonApi\ToOne()
             */
            public $name;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('More than 1 Relationship Annotation found for property `name`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionWhenResourceContainsAttributeAndRelationshipWithSameNames()
    {
        $resource = new class
        {
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

    public function testMetadataFactoryThrowsAnExceptionWhenResourceContainsRelationshipsWithSameNames()
    {
        $resource = new class
        {
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

    public function testMetadataFactoryThrowsAnExceptionWhenResourceContainsAttributesWithSameNames()
    {
        $resource = new class
        {
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

    public function testMetadataFactoryThrowsAnExceptionWhenTwoPropertyIsMappedWithTwoAttributeAnnotations()
    {
        $resource = new class
        {
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

    public function testMetadataFactoryThrowsAnExceptionForToOneRelationshipWithoutType()
    {
        $resource = new class
        {
            /**
             * @JsonApi\ToOne()
             */
            public $rel;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource type for `rel` is not defined');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForToManyRelationshipWithoutType()
    {
        $resource = new class
        {
            /**
             * @JsonApi\ToMany()
             */
            public $rel;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource type for `rel` is not defined');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForReservedIdAttribute()
    {
        $resource = new class
        {
            /** @JsonApi\Attribute() */
            public $id;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource can\'t use reserved attribute or relationship name `id`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForReservedIdToOneRelationship()
    {
        $resource = new class
        {
            /** @JsonApi\ToOne(type="test") */
            public $id;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource can\'t use reserved attribute or relationship name `id`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testMetadataFactoryThrowsAnExceptionForReservedIdToManyRelationship()
    {
        $resource = new class
        {
            /** @JsonApi\ToMany(type="test") */
            public $id;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->expectExceptionMessage('Resource can\'t use reserved attribute or relationship name `id`');
        $this->metadataFactory->getInstanceMetadata($resource);
    }

}
