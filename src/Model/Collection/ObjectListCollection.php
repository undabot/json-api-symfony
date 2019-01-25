<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use ArrayIterator;

class ObjectListCollection implements ObjectListCollectionInterface
{
    /** @var array */
    private $collection;

    /** @var int */
    private $count;

    public function __construct(array $collection, int $count)
    {
        $this->collection = $collection;
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
}
