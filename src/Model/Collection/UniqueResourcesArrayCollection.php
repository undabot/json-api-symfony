<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use ArrayIterator;
use Assert\Assertion;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class UniqueResourcesArrayCollection implements ResourceCollectionInterface
{
    /** @var ResourceInterface[] */
    private $elements;

    public function __construct(array $elements)
    {
        Assertion::allIsInstanceOf($elements, ResourceInterface::class);
        $this->elements = $elements;
    }

    /**
     * Add Resource to the collection and check whether the same combination of `id` and `type` already exists
     */
    public function addResourceIfItDoesntExist(ResourceInterface $resource)
    {
        $key = $resource->getId() . $resource->getType();
        if (false === isset($this->elements[$key])) {
            $this->elements[$key] = $resource;
        }
    }

    /**
     * @return ResourceInterface[]
     */
    public function getResources(): array
    {
        return $this->elements;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }
}
