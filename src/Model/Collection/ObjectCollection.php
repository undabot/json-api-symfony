<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use ArrayIterator;

class ObjectCollection implements ObjectCollectionInterface
{
    /** @var array */
    private $collection;

    /** @var int */
    private $count;

    public function __construct(array $collection, int $count = null)
    {
        $this->collection = $collection;
        if (null === $count) {
            $count = count($collection);
        }
        $this->count = $count;
    }

    public function getCollection(): array
    {
        return $this->collection;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getCollection());
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->collection[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->collection[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->collection[$offset]);
    }
}
