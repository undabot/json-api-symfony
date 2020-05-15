<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Model\Collection;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Model\Collection\ArrayCollection;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Model\Collection\ArrayCollection
 *
 * @small
 */
final class ArrayCollectionTest extends TestCase
{
    public function testConstructWillCountItemsGivenNoCount(): void
    {
        $items = ['a', 'b'];

        $arrayCollection = new ArrayCollection($items);

        static::assertEquals(2, $arrayCollection->count());
        static::assertEquals($items, $arrayCollection->getItems());
    }
}
