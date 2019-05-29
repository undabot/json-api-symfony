<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Resource\Annotated;

use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\AnnotatedResourceTrait;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation as JsonApi;

class AnnotatedResourceTraitAttributesTest extends TestCase
{
    public function testAnnotatedResourceReturnsAnnotatedAttributes()
    {
        $resource = new class
        {
            use AnnotatedResourceTrait;

            /**
             * @var string
             * @JsonApi\Attribute()
             */
            public $name;

            /**
             * @var string|null
             * @JsonApi\Attribute()
             */
            public $summary;

            /**
             * @var DateTimeImmutable
             * @JsonApi\Attribute()
             */
            public $publishedAt;

            /**
             * @var bool
             * @JsonApi\Attribute()
             */
            public $active;

            /**
             * @JsonApi\Attribute()
             */
            public $emptyAttribute;

            public $notAnAttribute;
        };

        $dateTime = new DateTimeImmutable();

        $resource->name = 'Resource Name';
        $resource->summary = 'Resource Summary';
        $resource->publishedAt = $dateTime;
        $resource->active = true;
        $resource->notAnAttribute = 'x';

        $attributes = $resource->getAttributes();

        $this->assertCount(5, $attributes);

        $this->assertSame('Resource Name', $attributes->getAttributeByName('name')->getValue());
        $this->assertSame('Resource Summary', $attributes->getAttributeByName('summary')->getValue());
        $this->assertSame(
            $dateTime->getTimestamp(),
            $attributes->getAttributeByName('publishedAt')->getValue()->getTimestamp()
        );
        $this->assertSame(true, $attributes->getAttributeByName('active')->getValue());
        $this->assertSame(null, $attributes->getAttributeByName('emptyAttribute')->getValue());
        $this->assertNull($attributes->getAttributeByName('notAnAttribute'));
    }

    public function testAnnotatedResourceThrowsAnExceptionWhenSinglePropertyHasMultipleRelationshipAnnotations()
    {
        $resource = new class
        {
            use AnnotatedResourceTrait;

            /**
             * @JsonApi\Attribute(name="tag")
             * @JsonApi\Attribute(name="tag2")
             */
            public $tag;
        };

        $this->expectException(LogicException::class);
        $resource->getAttributes();
    }

    public function testAnnotatedResourceReturnsEmptyCollectionWhenNoAttributesAreAnnotated()
    {
        $resource = new class
        {
            use AnnotatedResourceTrait;
        };

        $this->assertEmpty($resource->getAttributes()->getAttributes());
    }
}
