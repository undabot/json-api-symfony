<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Model\Resource;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Definition\Model\Link\LinkInterface;
use Undabot\JsonApi\Definition\Model\Meta\MetaInterface;
use Undabot\JsonApi\Definition\Model\Resource\Attribute\AttributeCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\RelationshipCollectionInterface;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Model\Resource\CombinedResource;
use Undabot\SymfonyJsonApi\Model\Resource\Exception\ResourceIdValueMismatch;
use Undabot\SymfonyJsonApi\Model\Resource\Exception\ResourceTypeValueMismatch;
use Undabot\SymfonyJsonApi\Model\Resource\FlatResource;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceRelationshipsBuilder;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
#[CoversNothing]
#[Small]
final class CombinedResourceTest extends TestCase
{
    public function testItReturnsCorrectTypeAndId(): void
    {
        $resource1 = new Resource('id', 'resource');
        $resource2 = new Resource('id', 'resource');

        $combinedResource = new CombinedResource($resource1, $resource2);

        self::assertSame('id', $combinedResource->getId());
        self::assertSame('resource', $combinedResource->getType());
    }

    public function testIncorrectResourceIdReferencesAreCaught(): void
    {
        $resource1 = new Resource('id1', 'resource');
        $resource2 = new Resource('id2', 'resource');

        $this->expectException(ResourceIdValueMismatch::class);
        new CombinedResource($resource1, $resource2);
    }

    public function testIncorrectResourceTypeReferencesAreCaught(): void
    {
        $resource1 = new Resource('id1', 'apples');
        $resource2 = new Resource('id1', 'oranges');

        $this->expectException(ResourceTypeValueMismatch::class);
        new CombinedResource($resource1, $resource2);
    }

    public function testItReturnsCorrectMetaAndSelfUrl(): void
    {
        $link = $this->createMock(LinkInterface::class);
        $meta = $this->createMock(MetaInterface::class);
        $resource1 = new Resource('id', 'resource', null, null, $link, $meta);
        $resource2 = new Resource('id', 'resource');

        $combinedResource = new CombinedResource($resource1, $resource2);

        self::assertSame($link, $combinedResource->getSelfUrl());
        self::assertSame($meta, $combinedResource->getMeta());
    }

    public function testItCorrectlyCombinesAttributes(): void
    {
        $resource1 = new Resource('id', 'resource', new AttributeCollection([
            new Attribute('attribute1', 'string'),
            new Attribute('attribute2', 1),
            new Attribute('attribute3', 2.0),
            new Attribute('attribute4', ['foo' => 'bar', 1, 2]),
            new Attribute('attribute5', null),
        ]));

        $resource2 = new Resource('id', 'resource', new AttributeCollection([
            new Attribute('attribute1', 'string_updated'),
            new Attribute('attribute3', 12.0),
            new Attribute('attribute4', null),
            new Attribute('attribute5', 'x'),
        ]));

        $combinedResource = new CombinedResource($resource1, $resource2);

        self::assertSame(
            $combinedResource->getAttributes()->getAttributeByName('attribute1')->getValue(),
            'string_updated'
        );
        self::assertSame(
            $combinedResource->getAttributes()->getAttributeByName('attribute2')->getValue(),
            1
        );
        self::assertSame(
            $combinedResource->getAttributes()->getAttributeByName('attribute3')->getValue(),
            12.0
        );
        self::assertNull($combinedResource->getAttributes()->getAttributeByName('attribute4')->getValue());
        self::assertSame(
            $combinedResource->getAttributes()->getAttributeByName('attribute5')->getValue(),
            'x'
        );
    }

    public function testItReturnsNullAttributesWhenBaseResourceIsWithoutAttributes(): void
    {
        $resource1 = new Resource('id', 'resource');

        $attributes = $this->createMock(AttributeCollectionInterface::class);
        $attributes->method('getIterator')
            ->willReturn(new \ArrayIterator());
        $resource2 = new Resource('id', 'resource', $attributes);

        $combinedResource = new CombinedResource($resource1, $resource2);
        self::assertNull($combinedResource->getAttributes());
    }

    public function testItReturnsBaseResourcesAttributesWhenOverlayedResourceIsWithoutAttributes(): void
    {
        $attributes = $this->createMock(AttributeCollectionInterface::class);
        $attributes->method('getIterator')
            ->willReturn(new \ArrayIterator());
        $resource1 = new Resource('id', 'resource', $attributes);
        $resource2 = new Resource('id', 'resource', null);
        $combinedResource = new CombinedResource($resource1, $resource2);
        self::assertSame($attributes, $combinedResource->getAttributes());
    }

    public function testItCorrectlyCombinesRelationships(): void
    {
        $resource1 = new Resource(
            'id',
            'resource',
            null,
            ResourceRelationshipsBuilder::make()
                ->toOne('r1', 'relatedResource1', '1')
                ->toMany('r2', 'relatedResource2', ['a', 'b', 'c'])
                ->toOne('r3', 'relatedResource3', 'x')
                ->get()
        );

        $resource2 = new Resource(
            'id',
            'resource',
            null,
            ResourceRelationshipsBuilder::make()
                ->toOne('r1', 'relatedResource1', '1_updated')
                ->toMany('r2', 'relatedResource2', ['a_updated', 'b_updated', 'c_updated'])
                ->get()
        );

        $combinedResource = new CombinedResource($resource1, $resource2);
        $flatResource = new FlatResource($combinedResource);

        self::assertEquals([
            'r1' => '1_updated',
            'r2' => ['a_updated', 'b_updated', 'c_updated'],
            'r3' => 'x',
        ], $flatResource->getRelationships());
    }

    public function testItReturnsNullRelationshipsWhenBaseResourceIsWithoutAttributes(): void
    {
        $resource1 = new Resource('id', 'resource');
        $relationships = $this->createMock(RelationshipCollectionInterface::class);
        $relationships->method('getIterator')
            ->willReturn(new \ArrayIterator());
        $resource2 = new Resource('id', 'resource', null, $relationships);

        $combinedResource = new CombinedResource($resource1, $resource2);
        self::assertNull($combinedResource->getRelationships());
    }

    public function testItReturnsBaseResourcesRelationshipsWhenOverlayedResourceIsWithoutAttributes(): void
    {
        $relationships = $this->createMock(RelationshipCollectionInterface::class);
        $relationships->method('getIterator')
            ->willReturn(new \ArrayIterator());
        $resource1 = new Resource('id', 'resource', null, $relationships);
        $resource2 = new Resource('id', 'resource');
        $combinedResource = new CombinedResource($resource1, $resource2);
        self::assertSame($relationships, $combinedResource->getRelationships());
    }

    public function testItThrowsExceptionWhenIncompatibleAttributesProvided(): void
    {
        $resource1 = new Resource('id', 'resource', new AttributeCollection([
            new Attribute('attribute1', 'string'),
            new Attribute('attribute2', 1),
            new Attribute('attribute3', 2.0),
            new Attribute('attribute4', ['foo' => 'bar', 1, 2]),
            new Attribute('attribute5', null),
        ]));

        $resource2 = new Resource('id', 'resource', new AttributeCollection([
            new Attribute('attribute1', 'string_updated'),
            new Attribute('attribute3', 12.0),
            new Attribute('attribute4', null),
            new Attribute('attribute5', 'x'),
            new Attribute('attribute6', 'y'),
        ]));

        $this->expectException(\InvalidArgumentException::class);
        new CombinedResource($resource1, $resource2);
    }
}
