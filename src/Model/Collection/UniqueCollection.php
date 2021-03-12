<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

/**
 * Collection of objects that assures only no duplicates will be added to it.
 * One instance of object will be added, while all others will be silently ignored.
 */
class UniqueCollection implements ObjectCollection
{
    /** @var array<string,object> */
    private array $items = [];

    /**
     * @param object[] $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->addObject($item);
        }
    }

    public function addObject(object $entity): void
    {
        $key = spl_object_hash($entity);
        if (false === isset($this->items[$key])) {
            $this->items[$key] = $entity;
        }
    }

    /**
     * @param object[] $entities
     */
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
        return \count($this->getItems());
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->getItems());
    }
}
