<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Resource\Convention;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\AnnotatedResourceTrait;
use Undabot\SymfonyJsonApi\Resource\Model\ConventionResourceTrait;

class ConventionResourceTraitAttributesTest extends TestCase
{
    public function testConventionResourceReturnsAnnotatedAttributes()
    {
        $resource = new class
        {
            use ConventionResourceTrait;

            public $name;
            public $summary;
            public $publishedAt;
            public $active;
            public $emptyAttribute;
            public $notAnAttribute;

            public function __construct()
            {
                $this->ignoredProperties[] = 'notAnAttribute';
            }
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

    public function testConventionResourceReturnsEmptyCollectionWhenNoAttributesDefined()
    {
        $resource = new class
        {
            use AnnotatedResourceTrait;
        };

        $this->assertEmpty($resource->getAttributes()->getAttributes());
    }

    public function testConventionResourceOverridesAttributeName()
    {
        $resource = new class
        {
            use ConventionResourceTrait;

            protected function modifyAttributeName(string $name): string
            {
                $map = [
                    'name' => 'newAttributeName',
                    'summary' => 'Summary',
                ];

                return $map[$name] ?? $name;
            }

            public $name;
            public $summary;
        };

        $resource->name = 'Resource Name';
        $resource->summary = 'Resource Summary';

        $attributes = $resource->getAttributes();

        $this->assertCount(2, $attributes);

        $this->assertNull($attributes->getAttributeByName('name'));
        $this->assertSame('Resource Name', $attributes->getAttributeByName('newAttributeName')->getValue());
        $this->assertNull($attributes->getAttributeByName('summary'));
        $this->assertSame('Resource Summary', $attributes->getAttributeByName('Summary')->getValue());
    }
}
