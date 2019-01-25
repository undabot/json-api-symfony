<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Relationship;

use Undabot\JsonApi\Model\Resource\Relationship\Data\ToManyRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToOneRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\Relationship;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\ResourceIdentifier;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierCollection;

class ResourceRelationshipsFactory
{
    /** @var array */
    private $toOne = [];

    /** @var array */
    private $toMany = [];

    public static function make()
    {
        return new self();
    }

    public function __construct()
    {
    }

    public function toOne(string $relationshipName, string $resourceType, string $id): self
    {
        $this->toOne[$relationshipName] = new ResourceIdentifier($id, $resourceType);

        return $this;
    }

    public function toMany(string $relationshipName, string $resourceType, array $ids): self
    {
        $this->toMany[$relationshipName] = [];
        foreach ($ids as $id) {
            $this->toMany[$relationshipName][] = new ResourceIdentifier($id, $resourceType);
        }

        return $this;
    }

    public function get(): RelationshipCollection
    {
        $relationships = [];
        foreach ($this->toOne as $relationshipName => $resourceIdentifier) {
            $relationships[] = new Relationship(
                $relationshipName,
                null,
                ToOneRelationshipData::make($resourceIdentifier)
            );
        }

        foreach ($this->toMany as $relationshipName => $resourceIdentifiers) {
            $resourceIdentifiersCollection = new ResourceIdentifierCollection($resourceIdentifiers);

            $relationships[] = new Relationship(
                $relationshipName,
                null,
                ToManyRelationshipData::make($resourceIdentifiersCollection)
            );
        }

        return new RelationshipCollection($relationships);
    }
}
