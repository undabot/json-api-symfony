<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Metadata;

use DateTimeImmutable;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\AttributeMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class ResourceAttributesMetadataTest extends TestCase
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

    public function testResourceMetadataContainsAllAnnotatedAttributes(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        static::assertInstanceOf(ResourceMetadata::class, $metadata);

        static::assertCount(5, $metadata->getAttributesMetadata());
        static::assertContainsOnlyInstancesOf(AttributeMetadata::class, $metadata->getAttributesMetadata());

        static::assertNotNull($metadata->getAttributeMetadata('name'));
        static::assertNotNull($metadata->getAttributeMetadata('summary'));
        static::assertNotNull($metadata->getAttributeMetadata('publishedAt'));
        static::assertNotNull($metadata->getAttributeMetadata('active'));
        static::assertNotNull($metadata->getAttributeMetadata('emptyAttribute'));
    }

    public function testResourceMetadataContainsValidAttributeConstraintsCount(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        static::assertInstanceOf(ResourceMetadata::class, $metadata);

        static::assertCount(5, $metadata->getAttributesMetadata());

        static::assertNotEmpty($metadata->getAttributeMetadata('name')->getConstraints());
        static::assertNotEmpty($metadata->getAttributeMetadata('summary')->getConstraints());
        static::assertNotEmpty($metadata->getAttributeMetadata('publishedAt')->getConstraints());
        static::assertNotEmpty($metadata->getAttributeMetadata('active')->getConstraints());
        static::assertEmpty($metadata->getAttributeMetadata('emptyAttribute')->getConstraints());
    }

    public function testResourceMetadataContainsValidNameAttributeConstraints(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        static::assertInstanceOf(ResourceMetadata::class, $metadata);

        $nameConstraints = $metadata->getAttributeMetadata('name')->getConstraints();
        static::assertNotEmpty($nameConstraints);
        static::assertCount(3, $nameConstraints);

        $nameConstraintExpectations = [
            Assert\NotBlank::class => 0,
            Assert\Length::class => 0,
            Assert\Type::class => 0,
        ];

        foreach ($nameConstraints as $constraint) {
            static::assertInstanceOf(Constraint::class, $constraint);
            ++$nameConstraintExpectations[\get_class($constraint)];

            if ($constraint instanceof Assert\Length) {
                static::assertSame(100, $constraint->min);
                static::assertSame(200, $constraint->max);
            }

            if ($constraint instanceof Assert\Type) {
                static::assertSame('string', $constraint->type);
            }
        }

        foreach ($nameConstraintExpectations as $constraintClass => $expectationCount) {
            static::assertSame(1, $expectationCount, $constraintClass);
        }
    }

    public function testResourceMetadataContainsValidPublishedAtAttributeConstraints(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        static::assertInstanceOf(ResourceMetadata::class, $metadata);

        $publishedAtConstraints = $metadata->getAttributeMetadata('publishedAt')->getConstraints();
        static::assertNotEmpty($publishedAtConstraints);
        static::assertCount(2, $publishedAtConstraints);

        $nameConstraintExpectations = [
            Assert\NotBlank::class => 0,
            Assert\Type::class => 0,
        ];

        foreach ($publishedAtConstraints as $constraint) {
            static::assertInstanceOf(Constraint::class, $constraint);
            ++$nameConstraintExpectations[\get_class($constraint)];

            if ($constraint instanceof Assert\Type) {
                static::assertSame('datetime', $constraint->type);
            }
        }

        foreach ($nameConstraintExpectations as $constraintClass => $expectationCount) {
            static::assertSame(1, $expectationCount, $constraintClass);
        }
    }

    public function testMetadataFactoryThrowsAnExceptionWhenSinglePropertyHasMultipleAttributeAnnotations(): void
    {
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\Attribute(name="tag")
             * @JsonApi\Attribute(name="tag2")
             */
            public $tag;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->metadataFactory->getInstanceMetadata($resource);
    }

    public function testAttributeNameCanBeOveridden(): void
    {
        /**
         * @ResourceType(type="resource")
         */
        $resource = new class() implements ApiModel {
            /** @JsonApi\Attribute */
            public $defaultName;

            /** @JsonApi\Attribute(name="overridenAttributeName") */
            public $defaultName2;
        };

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        static::assertInstanceOf(ResourceMetadata::class, $metadata);
        static::assertCount(2, $metadata->getAttributesMetadata());

        static::assertInstanceOf(AttributeMetadata::class, $metadata->getAttributeMetadata('defaultName'));

        static::assertNull($metadata->getAttributeMetadata('defaultName2'));
        static::assertInstanceOf(AttributeMetadata::class, $metadata->getAttributeMetadata('overridenAttributeName'));
    }

    public function testAttributeMetadataIsEmptyWhenNoAttributesAnnotated(): void
    {
        /**
         * @ResourceType(type="resource")
         */
        $resource = new class() implements ApiModel {
        };

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);
        static::assertTrue($metadata->getAttributesMetadata()->isEmpty());
    }

    private function getResource()
    {
        /**
         * @ResourceType(type="resource")
         */
        return new class() implements ApiModel {
            /**
             * @var string
             * @JsonApi\Attribute
             * @Assert\NotBlank
             * @Assert\Length(min=100, max=200)
             * @Assert\Type(type="string")
             */
            public $name;

            /**
             * @var null|string
             * @JsonApi\Attribute
             * @Assert\Type(type="string")
             */
            public $summary;

            /**
             * @var DateTimeImmutable
             * @JsonApi\Attribute
             * @Assert\NotBlank
             * @Assert\Type(type="datetime")
             */
            public $publishedAt;

            /**
             * @var bool
             * @JsonApi\Attribute
             * @Assert\NotBlank
             * @Assert\Type(type="bool")
             */
            public $active;

            /**
             * @JsonApi\Attribute
             */
            public $emptyAttribute;

            public $notAnAttribute;
        };
    }
}
