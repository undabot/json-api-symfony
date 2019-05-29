<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Integration\Resource\Metadata;

use DateTimeImmutable;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\AttributeMetadata;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\ResourceMetadata;

class ResourceAttributesMetadataTest extends TestCase
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
             * @var string
             * @JsonApi\Attribute()
             * @Assert\NotBlank()
             * @Assert\Length(min="100", max="200")
             * @Assert\Type(type="string")
             */
            public $name;

            /**
             * @var string|null
             * @JsonApi\Attribute()
             * @Assert\Type(type="string")
             */
            public $summary;

            /**
             * @var DateTimeImmutable
             * @JsonApi\Attribute()
             * @Assert\NotBlank()
             * @Assert\Type(type="datetime")
             */
            public $publishedAt;

            /**
             * @var bool
             * @JsonApi\Attribute()
             * @Assert\NotBlank()
             * @Assert\Type(type="bool")
             */
            public $active;

            /**
             * @JsonApi\Attribute()
             */
            public $emptyAttribute;

            public $notAnAttribute;
        };
    }

    public function testResourceMetadataContainsAllAnnotatedAttributes()
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $this->assertInstanceOf(ResourceMetadata::class, $metadata);

        $this->assertCount(5, $metadata->getAttributesMetadata());
        $this->assertContainsOnlyInstancesOf(AttributeMetadata::class, $metadata->getAttributesMetadata());

        $this->assertNotNull($metadata->getAttributeMetadata('name'));
        $this->assertNotNull($metadata->getAttributeMetadata('summary'));
        $this->assertNotNull($metadata->getAttributeMetadata('publishedAt'));
        $this->assertNotNull($metadata->getAttributeMetadata('active'));
        $this->assertNotNull($metadata->getAttributeMetadata('emptyAttribute'));
    }

    public function testResourceMetadataContainsValidAttributeConstraintsCount()
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $this->assertInstanceOf(ResourceMetadata::class, $metadata);

        $this->assertCount(5, $metadata->getAttributesMetadata());

        $this->assertNotEmpty($metadata->getAttributeMetadata('name')->getConstraints());
        $this->assertNotEmpty($metadata->getAttributeMetadata('summary')->getConstraints());
        $this->assertNotEmpty($metadata->getAttributeMetadata('publishedAt')->getConstraints());
        $this->assertNotEmpty($metadata->getAttributeMetadata('active')->getConstraints());
        $this->assertEmpty($metadata->getAttributeMetadata('emptyAttribute')->getConstraints());
    }

    public function testResourceMetadataContainsValidNameAttributeConstraints()
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $this->assertInstanceOf(ResourceMetadata::class, $metadata);

        $nameConstraints = $metadata->getAttributeMetadata('name')->getConstraints();
        $this->assertNotEmpty($nameConstraints);
        $this->assertCount(3, $nameConstraints);

        $nameConstraintExpectations = [
            Assert\NotBlank::class => 0,
            Assert\Length::class => 0,
            Assert\Type::class => 0,
        ];

        foreach ($nameConstraints as $constraint) {
            $this->assertInstanceOf(Constraint::class, $constraint);
            $nameConstraintExpectations[get_class($constraint)]++;

            if ($constraint instanceof Assert\Length) {
                $this->assertEquals(100, $constraint->min);
                $this->assertEquals(200, $constraint->max);
            }

            if ($constraint instanceof Assert\Type) {
                $this->assertEquals('string', $constraint->type);
            }
        }

        foreach ($nameConstraintExpectations as $constraintClass => $expectationCount) {
            $this->assertEquals(1, $expectationCount, $constraintClass);
        }
    }

    public function testResourceMetadataContainsValidPublishedAtAttributeConstraints()
    {
        $resource = $this->getResource();

        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $this->assertInstanceOf(ResourceMetadata::class, $metadata);

        $publishedAtConstraints = $metadata->getAttributeMetadata('publishedAt')->getConstraints();
        $this->assertNotEmpty($publishedAtConstraints);
        $this->assertCount(2, $publishedAtConstraints);

        $nameConstraintExpectations = [
            Assert\NotBlank::class => 0,
            Assert\Type::class => 0,
        ];

        foreach ($publishedAtConstraints as $constraint) {
            $this->assertInstanceOf(Constraint::class, $constraint);
            $nameConstraintExpectations[get_class($constraint)]++;

            if ($constraint instanceof Assert\Type) {
                $this->assertEquals('datetime', $constraint->type);
            }
        }

        foreach ($nameConstraintExpectations as $constraintClass => $expectationCount) {
            $this->assertEquals(1, $expectationCount, $constraintClass);
        }
    }

    public function testMetadataFactoryThrowsAnExceptionWhenSinglePropertyHasMultipleAttributeAnnotations()
    {
        $resource = new class
        {
            /**
             * @JsonApi\Attribute(name="tag")
             * @JsonApi\Attribute(name="tag2")
             */
            public $tag;
        };

        $this->expectException(InvalidResourceMappingException::class);
        $this->metadataFactory->getResourceMetadata($resource);
    }

    public function testAttributeNameCanBeOveridden()
    {
        $resource = new class
        {
            /** @JsonApi\Attribute() */
            public $defaultName;

            /** @JsonApi\Attribute(name="overridenAttributeName") */
            public $defaultName2;
        };

        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $this->assertInstanceOf(ResourceMetadata::class, $metadata);
        $this->assertCount(2, $metadata->getAttributesMetadata());

        $this->assertInstanceOf(AttributeMetadata::class, $metadata->getAttributeMetadata('defaultName'));

        $this->assertNull($metadata->getAttributeMetadata('defaultName2'));
        $this->assertInstanceOf(AttributeMetadata::class, $metadata->getAttributeMetadata('overridenAttributeName'));
    }

    public function testAttributeMetadataIsEmptyWhenNoAttributesAnnotated()
    {
        $resource = new class
        {
        };

        $metadata = $this->metadataFactory->getResourceMetadata($resource);
        $this->assertTrue($metadata->getAttributesMetadata()->isEmpty());
    }
}
