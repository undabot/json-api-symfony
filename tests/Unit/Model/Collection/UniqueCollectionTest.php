<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Model\Collection;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Model\Collection\UniqueCollection
 *
 * @small
 */
final class UniqueCollectionTest extends TestCase
{
    public function testConstructingUniqueCollectionWillSilentlyIgnoreDuplicatesGivenArrayWithDuplicateItems(): void
    {
        $class1 = new \stdClass();
        $class2 = new \stdClass();
        $class3 = new \stdClass();
        $class4 = new \stdClass();
        $collection = [$class1, $class2, $class3, $class4, $class1, $class1, $class2, $class3, $class4];
        $uniqueCollection = new UniqueCollection($collection);

        static::assertEquals([$class1, $class2, $class3, $class4], array_values($uniqueCollection->getItems()));
    }

    public function testAddObjectsWillNotStoreObjectAlreadyAddedWhileConstructingClassGivenArrayOfObjects(): void
    {
        $class1 = new \stdClass();
        $class2 = new \stdClass();
        $class3 = new \stdClass();
        $class4 = new \stdClass();
        $collection = [$class1, $class2, $class1, $class1, $class1, $class2];
        $uniqueCollection = new UniqueCollection($collection);
        $uniqueCollection->addObjects([$class1, $class2, $class1, $class1, $class1, $class2, $class3, $class3, $class4]);

        static::assertEquals([$class1, $class2, $class3, $class4], array_values($uniqueCollection->getItems()));
    }
}
