<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Model\Collection;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Model\Collection\ArrayCollection;

/**
 * @internal
 *
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

        self::assertEquals(2, $arrayCollection->count());
        self::assertEquals($items, $arrayCollection->getItems());
    }
}
