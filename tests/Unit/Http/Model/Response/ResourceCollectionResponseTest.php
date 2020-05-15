<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Http\Service\Factory;

use Assert\AssertionFailedException;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Definition\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Definition\Model\Link\LinkInterface;
use Undabot\JsonApi\Definition\Model\Meta\MetaInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Meta\Meta;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollection;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse
 *
 * @medium
 */
final class ResourceCollectionResponseTest extends TestCase
{
    public function testFromObjectCollectionCanCreateValidResourceCollectionResponseGivenAllArgumentsPresent(): void
    {
        $objectCollection = $this->createMock(ObjectCollection::class);
        $objectCollection->expects(static::once())->method('getItems')->willReturn([]);
        $includedResources = $this->createMock(ResourceCollectionInterface::class);
        $meta = $this->createMock(MetaInterface::class);
        $links = $this->createMock(LinkCollectionInterface::class);

        $resourceCollectionResponse = ResourceCollectionResponse::fromObjectCollection(
            $objectCollection,
            $includedResources,
            $meta,
            $links
        );

        static::assertInstanceOf(ResourceCollection::class, $resourceCollectionResponse->getPrimaryResources());
        static::assertEquals([], $resourceCollectionResponse->getPrimaryResources()->getResources());
        static::assertEquals($includedResources, $resourceCollectionResponse->getIncludedResources());
        static::assertEquals($meta, $resourceCollectionResponse->getMeta());
        static::assertEquals($links, $resourceCollectionResponse->getLinks());
    }

    public function testFromObjectCollectionCanCreateValidResourceCollectionResponseGivenOnlyObjectCollectionArg(): void
    {
        $objectCollection = $this->createMock(ObjectCollection::class);
        $objectCollection->expects(static::once())->method('getItems')->willReturn([]);

        $resourceCollectionResponse = ResourceCollectionResponse::fromObjectCollection($objectCollection);

        static::assertInstanceOf(ResourceCollection::class, $resourceCollectionResponse->getPrimaryResources());
        static::assertEquals([], $resourceCollectionResponse->getPrimaryResources()->getResources());
        static::assertNull($resourceCollectionResponse->getIncludedResources());
        static::assertEquals(new Meta(['total' => 0]), $resourceCollectionResponse->getMeta());
        static::assertNull($resourceCollectionResponse->getLinks());
    }

    /**
     * @dataProvider validResourceCollectionArrayArguments
     */
    public function testFromArrayCanCreateValidResourceCollectionResponseGivenValidArgumentsPresent(
        array $resources,
        ?array $included,
        ?array $meta,
        ?array $links
    ): void {
        $resourceCollectionResponse = ResourceCollectionResponse::fromArray(
            $resources,
            $included,
            $meta,
            $links
        );

        static::assertEquals($resources, $resourceCollectionResponse->getPrimaryResources()->getResources());
        static::assertEquals(
            $included,
            $resourceCollectionResponse->getIncludedResources()
                ? $resourceCollectionResponse->getIncludedResources()->getResources()
                : $resourceCollectionResponse->getIncludedResources()
        );
        static::assertEquals(
            $meta,
            $resourceCollectionResponse->getMeta()
                ? $resourceCollectionResponse->getMeta()->getData()
                : $resourceCollectionResponse->getMeta()
        );
        static::assertEquals(
            $links,
            $resourceCollectionResponse->getLinks()
                ? $resourceCollectionResponse->getLinks()->getLinks()
                : $resourceCollectionResponse->getLinks()
        );
    }

    public function validResourceCollectionArrayArguments(): \Generator
    {
        yield 'Only resources present' => [
            [$this->createMock(ResourceInterface::class), $this->createMock(ResourceInterface::class)],
            null,
            null,
            null,
        ];

        yield 'All arguments present' => [
            [$this->createMock(ResourceInterface::class), $this->createMock(ResourceInterface::class)],
            [$this->createMock(ResourceInterface::class), $this->createMock(ResourceInterface::class)],
            ['total' => 0],
            [$this->createMock(LinkInterface::class), $this->createMock(LinkInterface::class)],
        ];
    }

    /**
     * @dataProvider invalidResourceCollectionArrayArguments
     */
    public function testFromArrayWillThrowExceptionGivenInvalidArgumentsPresent(
        array $resources,
        ?array $included,
        ?array $meta,
        ?array $links,
        string $exceptionMessage
    ): void {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        ResourceCollectionResponse::fromArray(
            $resources,
            $included,
            $meta,
            $links
        );
    }

    public function invalidResourceCollectionArrayArguments(): \Generator
    {
        $objectCollection = $this->createMock(ObjectCollection::class);
        yield 'Resource array not valid type' => [
            [$objectCollection, $this->createMock(ResourceInterface::class)],
            null,
            null,
            null,
            'Class "' . \get_class($objectCollection) . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Resource\ResourceInterface" but is not.',
        ];

        yield 'Included array not valid type' => [
            [$this->createMock(ResourceInterface::class), $this->createMock(ResourceInterface::class)],
            [$objectCollection, $this->createMock(ResourceInterface::class)],
            null,
            null,
            'Class "' . \get_class($objectCollection) . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Resource\ResourceInterface" but is not.',
        ];

        yield 'Links array not valid type' => [
            [$this->createMock(ResourceInterface::class), $this->createMock(ResourceInterface::class)],
            [$this->createMock(ResourceInterface::class), $this->createMock(ResourceInterface::class)],
            null,
            [$objectCollection, $this->createMock(LinkInterface::class)],
            'Class "' . \get_class($objectCollection) . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Link\LinkInterface" but is not.',
        ];
    }
}
