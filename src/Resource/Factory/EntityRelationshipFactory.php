<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Factory;

use Doctrine\Common\Collections\Collection;
use Undabot\JsonApi\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Resource\Relationship\Data\RelationshipDataInterface;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToManyRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToManyRelationshipDataInterface;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToOneRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\Relationship;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollectionInterface;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierCollection;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierInterface;

class EntityRelationshipFactory
{
    /** @var EntityResourceFactoryResolver */
    private $resourceFactoryResolver;

    public function __construct(EntityResourceFactoryResolver $resourceFactoryResolver)
    {
        $this->resourceFactoryResolver = $resourceFactoryResolver;
    }

    private function buildSingleRelationshipIdentifier($entity): ResourceIdentifierInterface
    {
        $factory = $this->resourceFactoryResolver->resolve(get_class($entity));

        return $factory->createIdentifier($entity);
    }

    /**
     * $relationshipValue - either Entity (to one) or Collection (to many) value
     */
    private function buildRelationshipData($relationshipValue): RelationshipDataInterface
    {
        // @todo should we support other types such as arrays?
        if ($relationshipValue instanceof Collection) {
            return $this->buildToManyRelationshipData($relationshipValue);
        }

        return $this->buildToOneRelationshipData($relationshipValue);
    }

    private function buildToManyRelationshipData(Collection $relationshipValue): ToManyRelationshipDataInterface
    {
        $identifiers = [];
        foreach ($relationshipValue as $relationshipEntity) {
            $identifiers[] = $this->buildSingleRelationshipIdentifier($relationshipEntity);
        }

        return new ToManyRelationshipData(new ResourceIdentifierCollection($identifiers));
    }

    private function buildToOneRelationshipData($relationshipValue): RelationshipDataInterface
    {
        return ToOneRelationshipData::make($this->buildSingleRelationshipIdentifier($relationshipValue));
    }

    private function buildRelationship(
        string $relationshipName,
        $relationshipValue,
        ?LinkCollectionInterface $links = null,
        ?Meta $meta = null
    ): RelationshipInterface {
        return new Relationship(
            $relationshipName,
            $links,
            $this->buildRelationshipData($relationshipValue),
            $meta
        );
    }

    /**
     * Make RelationshipCollectionInterface from given array of relationship entities.
     *
     * @param array $relationships
     *
     * @return RelationshipCollectionInterface
     */
    public function makeRelationships(array $relationships): RelationshipCollectionInterface
    {
        $builtRelationships = [];
        foreach ($relationships as $name => $value) {
            $builtRelationships[] = $this->buildRelationship($name, $value);
        }

        return new RelationshipCollection($builtRelationships);
    }
}
