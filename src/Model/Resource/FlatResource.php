<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource;

use RuntimeException;
use Undabot\JsonApi\Definition\Model\Resource\Attribute\AttributeInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\Data\ToManyRelationshipDataInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\Data\ToOneRelationshipDataInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceIdentifierInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;

/**
 * FlatResource normalizes ResourceInterface attributes (AttributeCollectionInterface, AttributeInterface) and
 * relationships (RelationshipCollectionInterface, RelationshipInterface)
 * to simple key => value arrays that could be used for easier input handling.
 *
 * Since the ResourceInterface implementation must guard itself from invalid states and attribute/relationship names,
 * and also duplicated names of attributes/relationships and forbidden names.
 */
class FlatResource
{
    /** @var ResourceInterface */
    private $resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return array<string, null|string>
     */
    public function getAttributes(): array
    {
        $flatAttributes = [];

        if (null === $this->resource->getAttributes()) {
            return [];
        }

        /** @var AttributeInterface $attribute */
        foreach ($this->resource->getAttributes() as $attribute) {
            $flatAttributes[$attribute->getName()] = $attribute->getValue();
        }

        return $flatAttributes;
    }

    /**
     * @return array<string, null|string|string[]>
     */
    public function getRelationships(): array
    {
        $flatRelationships = [];

        if (null === $this->resource->getRelationships()) {
            return [];
        }

        /** @var RelationshipInterface $relationship */
        foreach ($this->resource->getRelationships() as $relationship) {
            $relationshipData = $relationship->getData();

            if (null === $relationshipData) {
                $flatRelationships[$relationship->getName()] = null;

                continue;
            }

            if ($relationshipData instanceof ToOneRelationshipDataInterface && true === $relationshipData->isEmpty()) {
                $flatRelationships[$relationship->getName()] = null;

                continue;
            }

            if ($relationshipData instanceof ToOneRelationshipDataInterface && false === $relationshipData->isEmpty()) {
                /** @var ResourceIdentifierInterface $data */
                $data = $relationshipData->getData();
                $flatRelationships[$relationship->getName()] = $data->getId();

                continue;
            }

            if ($relationshipData instanceof ToManyRelationshipDataInterface && true === $relationshipData->isEmpty()) {
                $flatRelationships[$relationship->getName()] = [];

                continue;
            }

            if ($relationshipData instanceof ToManyRelationshipDataInterface && false === $relationshipData->isEmpty()) {
                $flatData = array_map(static function (ResourceIdentifierInterface $resourceIdentifier) {
                    return $resourceIdentifier->getId();
                }, iterator_to_array($relationshipData->getData()));

                $flatRelationships[$relationship->getName()] = $flatData;

                continue;
            }

            throw new RuntimeException('Couldn\'t flatten the relationships');
        }

        return $flatRelationships;
    }

    /**
     * Unlike attributes, relationships are pairs of type and id values, and therefore cannot be simplified to a single
     * dimension array. Use this method to get pairs of relationship name and RelationshipInterface object.
     *
     * @return RelationshipInterface[]
     */
    public function getIndexedRelationshipObjects(): array
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
