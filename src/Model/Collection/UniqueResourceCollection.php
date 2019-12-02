<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use ArrayIterator;
use Assert\Assertion;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;

class UniqueResourceCollection implements ResourceCollectionInterface
{
    /** @var ResourceInterface[] */
    private $items;

    /**
     * @param ResourceInterface[] $resource
     */
    public function __construct(array $resource = [])
    {
        Assertion::allIsInstanceOf($resource, ResourceInterface::class);
        $this->items = $resource;
    }

    /**
     * Add Resource to the collection and check whether the same combination of `id` and `type` already exists.
     */
    public function addResourceIfItDoesntExist(ResourceInterface $resource): void
    {
        $key = $resource->getId().$resource->getType();
        if (false === isset($this->items[$key])) {
            $this->items[$key] = $resource;
        }
    }

    /**
     * @param ResourceInterface[] $resources
     */
    public function addResourcesIfTheyDontExist(array $resources): void
    {
        Assertion::allIsInstanceOf($resources, ResourceInterface::class);
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
