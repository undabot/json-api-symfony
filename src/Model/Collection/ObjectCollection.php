<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use ArrayIterator;

class ObjectCollection implements ObjectCollectionInterface
{
    /** @var array */
    private $items;

    /** @var int */
    private $count;

    public function __construct(array $items, int $count = null)
    {
        $this->items = $items;
        if (null === $count) {
            $count = count($items);
        }
        $this->count = $count;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getItems());
    }
//
//    public function offsetExists($offset): bool
//    {
//        return array_key_exists($offset, $this->items[$offset]);
//    }
//
//    public function offsetGet($offset)
//    {
//        return $this->items[$offset];
//    }
//
//    public function offsetSet($offset, $value): void
//    {
//        $this->items[$offset] = $value;
//    }
//
//    public function offsetUnset($offset): void
//    {
//        unset($this->items[$offset]);
//    }
}
