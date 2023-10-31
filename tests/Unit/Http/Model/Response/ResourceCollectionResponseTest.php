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
 *
 * @covers \Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse
 *
 * @medium
 */
final class ResourceCollectionResponseTest extends TestCase
{
    public function testFromObjectCollectionCanCreateValidResourceCollectionResponseGivenAllArgumentsPresent(): void
    {
        $objectCollection = $this->createMock(ObjectCollection::class);
        $objectCollection->expects(self::once())->method('getItems')->willReturn([]);
        $includedResources = $this->createMock(ResourceCollectionInterface::class);
        $meta = $this->createMock(MetaInterface::class);
        $links = $this->createMock(LinkCollectionInterface::class);

        $resourceCollectionResponse = ResourceCollectionResponse::fromObjectCollection(
            $objectCollection,
            $includedResources,
            $meta,
            $links
        );

        self::assertInstanceOf(ResourceCollection::class, $resourceCollectionResponse->getPrimaryResources());
        self::assertEquals([], $resourceCollectionResponse->getPrimaryResources()->getResources());
        self::assertEquals($includedResources, $resourceCollectionResponse->getIncludedResources());
        self::assertEquals($meta, $resourceCollectionResponse->getMeta());
        self::assertEquals($links, $resourceCollectionResponse->getLinks());
    }

    public function testFromObjectCollectionCanCreateValidResourceCollectionResponseGivenOnlyObjectCollectionArg(): void
    {
        $objectCollection = $this->createMock(ObjectCollection::class);
        $objectCollection->expects(self::once())->method('getItems')->willReturn([]);

        $resourceCollectionResponse = ResourceCollectionResponse::fromObjectCollection($objectCollection);

        self::assertInstanceOf(ResourceCollection::class, $resourceCollectionResponse->getPrimaryResources());
        self::assertEquals([], $resourceCollectionResponse->getPrimaryResources()->getResources());
        self::assertNull($resourceCollectionResponse->getIncludedResources());
        self::assertEquals(new Meta(['total' => 0]), $resourceCollectionResponse->getMeta());
        self::assertNull($resourceCollectionResponse->getLinks());
    }

    /**
     * @dataProvider provideFromArrayCanCreateValidResourceCollectionResponseGivenValidArgumentsPresentCases
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

        self::assertEquals($resources, $resourceCollectionResponse->getPrimaryResources()->getResources());
        self::assertEquals(
            $included,
            $resourceCollectionResponse->getIncludedResources()
                ? $resourceCollectionResponse->getIncludedResources()->getResources()
                : $resourceCollectionResponse->getIncludedResources()
        );
        self::assertEquals(
            $meta,
            $resourceCollectionResponse->getMeta()
                ? $resourceCollectionResponse->getMeta()->getData()
                : $resourceCollectionResponse->getMeta()
        );
        self::assertEquals(
            $links,
            $resourceCollectionResponse->getLinks()
                ? $resourceCollectionResponse->getLinks()->getLinks()
                : $resourceCollectionResponse->getLinks()
        );
    }

    public function provideFromArrayCanCreateValidResourceCollectionResponseGivenValidArgumentsPresentCases(): iterable
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
     * @dataProvider provideFromArrayWillThrowExceptionGivenInvalidArgumentsPresentCases
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

    public function provideFromArrayWillThrowExceptionGivenInvalidArgumentsPresentCases(): iterable
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
