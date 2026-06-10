<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

class ArrayCollection implements ObjectCollection
{
    /** @var int */
    private $count;

    /**
     * @param mixed[] $items
     */
    public function __construct(private readonly array $items, ?int $count = null)
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

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->getItems());
    }
}
