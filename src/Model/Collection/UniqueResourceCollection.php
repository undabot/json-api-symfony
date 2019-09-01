<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use ArrayIterator;
use Assert\Assertion;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class UniqueResourceCollection implements ResourceCollectionInterface
{
    /** @var ResourceInterface[] */
    private $items;

    public function __construct(array $items = [])
    {
        Assertion::allIsInstanceOf($items, ResourceInterface::class);
        $this->items = $items;
    }

    /**
     * Add Resource to the collection and check whether the same combination of `id` and `type` already exists
     */
    public function addResourceIfItDoesntExist(ResourceInterface $resource): void
    {
        $key = $resource->getId() . $resource->getType();
        if (false === isset($this->items[$key])) {
            $this->items[$key] = $resource;
        }
    }

    public function addResourcesIfTheyDontExist(array $resources): void
    {
        foreach ($resources as $resource) {
            $this->addResourceIfItDoesntExist($resource);
        }
    }

    /**
     * @return ResourceInterface[]
     */
    public function getResources(): array
    {
        return $this->items;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}
