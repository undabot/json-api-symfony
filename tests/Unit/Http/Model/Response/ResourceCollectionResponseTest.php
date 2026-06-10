<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Http\Service\Factory;

use Assert\AssertionFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Definition\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Definition\Model\Meta\MetaInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Implementation\Model\Link\Link;
use Undabot\JsonApi\Implementation\Model\Link\LinkUrl;
use Undabot\JsonApi\Implementation\Model\Meta\Meta;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollection;

/**
 * @internal
 */
#[CoversClass(ResourceCollectionResponse::class)]
#[Medium]
final class ResourceCollectionResponseTest extends TestCase
{
    public function testFromObjectCollectionCanCreateValidResourceCollectionResponseGivenAllArgumentsPresent(): void
    {
        $objectCollection = $this->createMock(ObjectCollection::class);
        $objectCollection->expects(self::once())->method('getItems')->willReturn([]);
        $includedResources = self::createStub(ResourceCollectionInterface::class);
        $meta = self::createStub(MetaInterface::class);
        $links = self::createStub(LinkCollectionInterface::class);

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

    public static function provideFromArrayCanCreateValidResourceCollectionResponseGivenValidArgumentsPresentCases(): iterable
    {
        yield 'Only resources present' => [
            [new Resource('1', 'resource'), new Resource('2', 'resource')],
            null,
            null,
            null,
        ];

        yield 'All arguments present' => [
            [new Resource('1', 'resource'), new Resource('2', 'resource')],
            [new Resource('1', 'resource'), new Resource('2', 'resource')],
            ['total' => 0],
            [new Link('self', new LinkUrl('https://example.com/resource/1')), new Link('related', new LinkUrl('https://example.com/resource/2'))],
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

        ResourceCollectionResponse::fromArray(
            $resources,
            $included,
            $meta,
            $links
        );
    }

    public static function provideFromArrayWillThrowExceptionGivenInvalidArgumentsPresentCases(): iterable
    {
        $invalidItem = new \stdClass();

        yield 'Resource array not valid type' => [
            [$invalidItem, new Resource('1', 'resource')],
            null,
            null,
            null,
            'Class "' . $invalidItem::class . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Resource\ResourceInterface" but is not.',
        ];

        yield 'Included array not valid type' => [
            [new Resource('1', 'resource'), new Resource('2', 'resource')],
            [$invalidItem, new Resource('1', 'resource')],
            null,
            null,
            'Class "' . $invalidItem::class . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Resource\ResourceInterface" but is not.',
        ];

        yield 'Links array not valid type' => [
            [new Resource('1', 'resource'), new Resource('2', 'resource')],
            [new Resource('1', 'resource'), new Resource('2', 'resource')],
            null,
            [$invalidItem, new Link('self', new LinkUrl('https://example.com/resource/1'))],
            'Class "' . $invalidItem::class . '" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Link\LinkInterface" but is not.',
        ];
    }
}
