<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Resource\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
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
 *
 * @coversNothing
 *
 * @small
 */
#[CoversNothing]
#[Small]
final class ResourceAttributesMetadataTest extends TestCase
{
    /** @var ResourceMetadataFactory */
    private $metadataFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $annotationReader = new AnnotationReader();
        $this->metadataFactory = new ResourceMetadataFactory($annotationReader);
    }

    public function testResourceMetadataContainsAllAnnotatedAttributes(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        self::assertInstanceOf(ResourceMetadata::class, $metadata);

        self::assertCount(5, $metadata->getAttributesMetadata());
        self::assertContainsOnlyInstancesOf(AttributeMetadata::class, $metadata->getAttributesMetadata());

        self::assertNotNull($metadata->getAttributeMetadata('name'));
        self::assertNotNull($metadata->getAttributeMetadata('summary'));
        self::assertNotNull($metadata->getAttributeMetadata('publishedAt'));
        self::assertNotNull($metadata->getAttributeMetadata('active'));
        self::assertNotNull($metadata->getAttributeMetadata('emptyAttribute'));
    }

    public function testResourceMetadataContainsValidAttributeConstraintsCount(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        self::assertInstanceOf(ResourceMetadata::class, $metadata);

        self::assertCount(5, $metadata->getAttributesMetadata());

        self::assertNotEmpty($metadata->getAttributeMetadata('name')->getConstraints());
        self::assertNotEmpty($metadata->getAttributeMetadata('summary')->getConstraints());
        self::assertNotEmpty($metadata->getAttributeMetadata('publishedAt')->getConstraints());
        self::assertNotEmpty($metadata->getAttributeMetadata('active')->getConstraints());
        self::assertEmpty($metadata->getAttributeMetadata('emptyAttribute')->getConstraints());
    }

    public function testResourceMetadataContainsValidNameAttributeConstraints(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        self::assertInstanceOf(ResourceMetadata::class, $metadata);

        $nameConstraints = $metadata->getAttributeMetadata('name')->getConstraints();
        self::assertNotEmpty($nameConstraints);
        self::assertCount(3, $nameConstraints);

        $nameConstraintExpectations = [
            Assert\NotBlank::class => 0,
            Assert\Length::class => 0,
            Assert\Type::class => 0,
        ];

        foreach ($nameConstraints as $constraint) {
            self::assertInstanceOf(Constraint::class, $constraint);
            ++$nameConstraintExpectations[\get_class($constraint)];

            if ($constraint instanceof Assert\Length) {
                self::assertSame(100, $constraint->min);
                self::assertSame(200, $constraint->max);
            }

            if ($constraint instanceof Assert\Type) {
                self::assertSame('string', $constraint->type);
            }
        }

        foreach ($nameConstraintExpectations as $constraintClass => $expectationCount) {
            self::assertSame(1, $expectationCount, $constraintClass);
        }
    }

    public function testResourceMetadataContainsValidPublishedAtAttributeConstraints(): void
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);

        self::assertInstanceOf(ResourceMetadata::class, $metadata);

        $publishedAtConstraints = $metadata->getAttributeMetadata('publishedAt')->getConstraints();
        self::assertNotEmpty($publishedAtConstraints);
        self::assertCount(2, $publishedAtConstraints);

        $nameConstraintExpectations = [
            Assert\NotBlank::class => 0,
            Assert\Type::class => 0,
        ];

        foreach ($publishedAtConstraints as $constraint) {
            self::assertInstanceOf(Constraint::class, $constraint);
            ++$nameConstraintExpectations[\get_class($constraint)];

            if ($constraint instanceof Assert\Type) {
                self::assertSame('datetime', $constraint->type);
            }
        }

        foreach ($nameConstraintExpectations as $constraintClass => $expectationCount) {
            self::assertSame(1, $expectationCount, $constraintClass);
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

        self::assertInstanceOf(ResourceMetadata::class, $metadata);
        self::assertCount(2, $metadata->getAttributesMetadata());

        self::assertInstanceOf(AttributeMetadata::class, $metadata->getAttributeMetadata('defaultName'));

        self::assertNull($metadata->getAttributeMetadata('defaultName2'));
        self::assertInstanceOf(AttributeMetadata::class, $metadata->getAttributeMetadata('overridenAttributeName'));
    }

    public function testAttributeMetadataIsEmptyWhenNoAttributesAnnotated(): void
    {
        /**
         * @ResourceType(type="resource")
         */
        $resource = new class() implements ApiModel {};

        $metadata = $this->metadataFactory->getInstanceMetadata($resource);
        self::assertTrue($metadata->getAttributesMetadata()->isEmpty());
    }

    private function getResource()
    {
        /**
         * @ResourceType(type="resource")
         */
        return new class() implements ApiModel {
            /**
             * @var string
             *
             * @JsonApi\Attribute
             *
             * @Assert\NotBlank
             *
             * @Assert\Length(min=100, max=200)
             *
             * @Assert\Type(type="string")
             */
            public $name;

            /**
             * @var null|string
             *
             * @JsonApi\Attribute
             *
             * @Assert\Type(type="string")
             */
            public $summary;

            /**
             * @var \DateTimeImmutable
             *
             * @JsonApi\Attribute
             *
             * @Assert\NotBlank
             *
             * @Assert\Type(type="datetime")
             */
            public $publishedAt;

            /**
             * @var bool
             *
             * @JsonApi\Attribute
             *
             * @Assert\NotBlank
             *
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
