<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Model\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Model\Collection\ArrayCollection;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
#[CoversClass('\Undabot\SymfonyJsonApi\Model\Collection\ArrayCollection')]
#[Small]
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
