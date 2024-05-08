<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use Traversable;

class ArrayCollection implements ObjectCollection
{
    private ?int $count;

    /**
     * @param mixed[] $items
     */
    public function __construct(private array $items, ?int $count = null)
    {
        if (null === $count) {
            $count = \count($this->items);
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

    /**
     * @return \Traversable<mixed, mixed> a traversable collection of items
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->getItems());
    }
}
