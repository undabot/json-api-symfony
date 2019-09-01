<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use ArrayIterator;

class UniqueCollection implements ObjectCollection
{
    /** @var array */
    private $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->addObject($item);
        }
    }

    public function addObject($entity): void
    {
        $key = spl_object_hash($entity);
        if (false === isset($this->items[$key])) {
            $this->items[$key] = $entity;
        }
    }

    public function addObjects(array $entities): void
    {
        foreach ($entities as $resource) {
            $this->addObject($resource);
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->getItems());
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getItems());
    }
}
