<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Http\Service\Factory;

use Assert\AssertionFailedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Definition\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Definition\Model\Link\LinkInterface;
use Undabot\JsonApi\Definition\Model\Meta\MetaInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Meta\Meta;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Model\Collection\ArrayCollection;
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

    #[DataProvider('provideFromArrayCanCreateValidResourceCollectionResponseGivenValidArgumentsPresentCases')]
    public function testFromArrayCanCreateValidResourceCollectionResponseGivenValidArgumentsPresent(
        array $resources,
        ?array $included,
        ?array $meta,
        ?array $links
    ): void {
        $resourceMocks = [];
        foreach ($resources as $resource) {
            $resourceMocks[] = $this->createMock($resource);
        }
        $includeMocks = null;
        if (null !== $included) {
            $includeMocks = [];
            foreach ($included as $include) {
                $includeMocks[] = $this->createMock($include);
            }
        }
        $linkMocks = null;
        if (null !== $links) {
            $linkMocks = [];
            foreach ($links as $link) {
                $linkMocks[] = $this->createMock($link);
            }
        }
        $resourceCollectionResponse = ResourceCollectionResponse::fromArray(
            $resourceMocks,
            null === $included ? null : $includeMocks,
            $meta,
            null === $links ? null : $linkMocks,
        );

        self::assertEquals($resourceMocks, $resourceCollectionResponse->getPrimaryResources()->getResources());
        self::assertEquals(
            $includeMocks,
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
            $linkMocks,
            $resourceCollectionResponse->getLinks()
                ? $resourceCollectionResponse->getLinks()->getLinks()
                : $resourceCollectionResponse->getLinks()
        );
    }

    public static function provideFromArrayCanCreateValidResourceCollectionResponseGivenValidArgumentsPresentCases(): iterable
    {
        yield 'Only resources present' => [
            [ResourceInterface::class, ResourceInterface::class],
            null,
            null,
            null,
        ];

        yield 'All arguments present' => [
            [ResourceInterface::class, ResourceInterface::class],
            [ResourceInterface::class, ResourceInterface::class],
            ['total' => 0],
            [LinkInterface::class, LinkInterface::class],
        ];
    }

    #[DataProvider('provideFromArrayWillThrowExceptionGivenInvalidArgumentsPresentCases')]
    public function testFromArrayWillThrowExceptionGivenInvalidArgumentsPresent(
        array $resources,
        ?array $included,
        ?array $meta,
        ?array $links,
        string $exceptionMessage
    ): void {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $resourceMocks = [];
        $includeMocks = [];
        foreach ($resources as $resource) {
            if (true === \is_string($resource)) {
                $resourceMocks[] = $this->createMock($resource);
            } else {
                $resourceMocks[] = $resource;
            }
        }

        foreach ($included as $include) {
            if (true === \is_string($include)) {
                $includeMocks[] = $this->createMock($include);
            } else {
                $includeMocks[] = $include;
            }
        }

        ResourceCollectionResponse::fromArray(
            $resourceMocks,
            $includeMocks,
            $meta,
            $links
        );
    }

    public static function provideFromArrayWillThrowExceptionGivenInvalidArgumentsPresentCases(): iterable
    {
        $objectCollection = new ArrayCollection([]);

        yield 'Resource array not valid type' => [
            [$objectCollection, ResourceInterface::class],
            null,
            null,
            null,
            'Class "' . \get_class($objectCollection) . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Resource\ResourceInterface" but is not.',
        ];

        yield 'Included array not valid type' => [
            [ResourceInterface::class, ResourceInterface::class],
            [$objectCollection, ResourceInterface::class],
            null,
            null,
            'Class "' . \get_class($objectCollection) . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Resource\ResourceInterface" but is not.',
        ];

        yield 'Links array not valid type' => [
            [ResourceInterface::class, ResourceInterface::class],
            [ResourceInterface::class, ResourceInterface::class],
            null,
            [$objectCollection, LinkInterface::class],
            'Class "' . \get_class($objectCollection) . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Link\LinkInterface" but is not.',
        ];
    }
}
