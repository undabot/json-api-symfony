<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Performance\Resource;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Model\Link\LinkInterface;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollectionInterface;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\AnnotatedResourceTrait;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation as JsonApi;

class ResourcePerformanceTest extends TestCase
{
    private function getAnnotatedResource()
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

            public function getId(): string
            {
                return 'id';
            }

            public function getType(): string
            {
                return 'resource';
            }
        };

        return $resource;
    }

    private function getResource()
    {
        $resource = new class implements ResourceInterface
        {
            /**
             * @var string
             */
            public $name;

            /**
             * @var string|null
             */
            public $summary;

            /**
             * @var DateTimeImmutable
             */
            public $publishedAt;

            /**
             * @var bool
             */
            public $active;

            public $emptyAttribute;

            public function getId(): string
            {
                return 'id';
            }

            public function getType(): string
            {
                return 'resource';
            }

            public function getAttributes(): ?AttributeCollectionInterface
            {
                return new AttributeCollection([
                    new Attribute('name', $this->name),
                    new Attribute('summary', $this->summary),
                    new Attribute('publishedAt', $this->publishedAt),
                    new Attribute('active', $this->active),
                    new Attribute('emptyAttribute', $this->emptyAttribute),
                ]);
            }

            public function getRelationships(): ?RelationshipCollectionInterface
            {
                return null;
            }

            public function getSelfUrl(): ?LinkInterface
            {
                return null;
            }

            public function getMeta(): ?MetaInterface
            {
                return null;
            }
        };

        return $resource;
    }

    private function populateData($resource)
    {
        $dateTime = new DateTimeImmutable();
        $resource->name = 'Resource Name';
        $resource->summary = 'Resource Summary';
        $resource->publishedAt = $dateTime;
        $resource->active = true;
    }

    public function testAnnotatedResourcePerformance()
    {
        $this->assertTrue(true);

        $timeStart = microtime(true);

        $resource = $this->getAnnotatedResource();
        $this->populateData($resource);

        for ($i = 0; $i < 1000; $i++) {
            $attributes = $resource->getAttributes();
        }

        $timeEnd = microtime(true);
        $memoryEnd = memory_get_usage(true);
        $peakMemoryEnd = memory_get_peak_usage(true);

        var_dump('Time', $timeEnd - $timeStart);
        var_dump('Memory', $memoryEnd / 1024 / 1024);
        var_dump('Peak Memory', $peakMemoryEnd / 1024 / 1024);
    }

    public function testResourcePerformance()
    {
        $this->assertTrue(true);

        $timeStart = microtime(true);

        $resource = $this->getResource();
        $this->populateData($resource);

        for ($i = 0; $i < 1000; $i++) {
            $attributes = $resource->getAttributes();
        }

        $timeEnd = microtime(true);
        $memoryEnd = memory_get_usage(true);
        $peakMemoryEnd = memory_get_peak_usage(true);

        var_dump('Time', $timeEnd - $timeStart);
        var_dump('Memory', $memoryEnd / 1024 / 1024);
        var_dump('Peak Memory', $peakMemoryEnd / 1024 / 1024);
    }
}
