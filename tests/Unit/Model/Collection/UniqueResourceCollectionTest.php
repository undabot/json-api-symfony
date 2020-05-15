<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Model\Collection;

use Assert\AssertionFailedException;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\Collection\UniqueResourceCollection;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Model\Collection\UniqueResourceCollection
 *
 * @medium
 */
final class UniqueResourceCollectionTest extends TestCase
{
    public function testConstructingUniqueResourceCollectionWillThrowExceptionGivenResourcesNotCorrectType(): void
    {
        $class1 = new \stdClass();
        $class2 = new \stdClass();

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Class "stdClass" was expected to be instanceof of "Undabot\JsonApi\Definition\Model\Resource\ResourceInterface" but is not.');

        new UniqueResourceCollection([$class1, $class2]);
    }

    public function testAddResourceIfItDoesntExistWillNotAddResourceGivenResourceAlreadyExist(): void
    {
        $class1 = $this->createMock(ResourceInterface::class);
        $class2 = $this->createMock(ResourceInterface::class);
        $class3 = $this->createMock(ResourceInterface::class);
        $class4 = $this->createMock(ResourceInterface::class);

        $class1->expects(static::once())->method('getId')->willReturn('22');
        $class2->expects(static::once())->method('getId')->willReturn('33');
        $class1->expects(static::once())->method('getType')->willReturn('abc');
        $class2->expects(static::once())->method('getType')->willReturn('cde');
        $class3->expects(static::exactly(2))->method('getId')->willReturn('1');
        $class3->expects(static::exactly(2))->method('getType')->willReturn('foo');
        $class4->expects(static::once())->method('getId')->willReturn('2');
        $class4->expects(static::once())->method('getType')->willReturn('bar');

        $uniqueResourceCollection = new UniqueResourceCollection([$class1, $class2]);

        $uniqueResourceCollection->addResourceIfItDoesntExist($class3);
        $uniqueResourceCollection->addResourceIfItDoesntExist($class3);
        $uniqueResourceCollection->addResourceIfItDoesntExist($class4);

        static::assertEquals([$class1, $class2, $class3, $class4], array_values($uniqueResourceCollection->getResources()));
        static::assertEquals(['22abc', '33cde', '1foo', '2bar'], array_keys($uniqueResourceCollection->getResources()));
    }
}
