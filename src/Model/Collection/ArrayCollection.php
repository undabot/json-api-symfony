<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use ArrayIterator;

class ArrayCollection implements ObjectCollection
{
    /** @var array */
    private $items;

    /** @var int */
    private $count;

    /**
     * @param mixed[] $items
     */
    public function __construct(array $items, int $count = null)
    {
        $this->items = $items;
        if (null === $count) {
            $count = \count($items);
        }
        $this->count = $count;
    }

    /**
     * @return mixed[] $items
     */
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
}
