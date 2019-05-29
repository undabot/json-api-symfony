<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource;

use Undabot\JsonApi\Model\Resource\Attribute\AttributeInterface;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToManyRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToOneRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class FlatResource
{
    /** @var ResourceInterface */
    private $resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    public function getAttributes(): array
    {
        $flatAttributes = [];

        if (null === $this->resource->getAttributes()) {
            return [];
        }

        $originalAttributes = iterator_to_array($this->resource->getAttributes());

        /** @var AttributeInterface $attribute */
        foreach ($originalAttributes as $attribute) {
            $flatAttributes[$attribute->getName()] = $attribute->getValue();
        }

        return $flatAttributes;
    }

    public function getRelationships(): array
    {
        $flatRelationships = [];

        if (null === $this->resource->getRelationships()) {
            return [];
        }

        $originalRelationships = iterator_to_array($this->resource->getRelationships());

        /** @var RelationshipInterface $relationship */
        foreach ($originalRelationships as $relationship) {
            $data = $relationship->getData();

            if (null === $data) {
                $flatRelationships[$relationship->getName()] = null;
                continue;
            }

            if ($data instanceof ToOneRelationshipData && true === $data->isEmpty()) {
                $flatRelationships[$relationship->getName()] = null;
                continue;
            }

            if ($data instanceof ToOneRelationshipData && false === $data->isEmpty()) {
                $flatRelationships[$relationship->getName()] = $data->getData()->getId();
                continue;
            }

            if ($data instanceof ToManyRelationshipData && true === $data->isEmpty()) {
                $flatRelationships[$relationship->getName()] = [];
                continue;
            }

            if ($data instanceof ToManyRelationshipData && false === $data->isEmpty()) {
                $flatData = array_map(function (ResourceIdentifierInterface $resourceIdentifier) {
                    return $resourceIdentifier->getId();
                }, iterator_to_array($data->getData()));

                $flatRelationships[$relationship->getName()] = $flatData;
                continue;
            }

            throw new \RuntimeException('Couldn\'t flatten the relationships');
        }

        return $flatRelationships;
    }

    /**
     * @return RelationshipInterface[]
     */
    public function getIndexedRelationshipObjects()
    {
        $flatRelationships = [];

        if (null === $this->resource->getRelationships()) {
            return [];
        }

        $originalRelationships = iterator_to_array($this->resource->getRelationships());

        /** @var RelationshipInterface $relationship */
        foreach ($originalRelationships as $relationship) {
            $flatRelationships[$relationship->getName()] = $relationship;
        }

        return $flatRelationships;
    }
}
