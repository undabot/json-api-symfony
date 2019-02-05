<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class UniqueResourcesArrayCollection extends ArrayCollection implements ResourceCollectionInterface
{
    /**
     * Add Resource to the collection and check whether the same combination of `id` and `type` already exists
     */
    public function addResourceIfItDoesntExist(ResourceInterface $resource)
    {
        $key = $resource->getId() . $resource->getType();
        if (false === $this->containsKey($key)) {
            $this->set($key, $resource);
        }
    }

    public function getResources(): array
    {
        return $this->toArray();
    }
}
