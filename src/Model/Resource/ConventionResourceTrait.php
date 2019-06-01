<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Model;

use Assert\Assertion;
use Undabot\JsonApi\Factory\RelationshipDataFactory;
use Undabot\JsonApi\Model\Link\LinkInterface;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollectionInterface;
use Undabot\JsonApi\Model\Resource\Relationship\Relationship;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollectionInterface;

trait ConventionResourceTrait
{
    protected $ignoredProperties = [
        'id',
        'ignoredProperties',
    ];

    protected function modifyRelationshipName(string $name): string
    {
        return $name;
    }

    protected function modifyAttributeName(string $name): string
    {
        return $name;
    }

    /**
     * Relationship property ends with `Id` or `Ids`
     * e.g. tagIds, authorId
     */
    private function isRelationship(string $propertyName)
    {
        return substr($propertyName, -2) === 'Id' || substr($propertyName, -3) === 'Ids';
    }

    private function getProperties(): array
    {
        $properties = get_object_vars($this);
        $properties = array_filter($properties, function ($value, $key) {
            return false === in_array($key, $this->ignoredProperties);
        }, ARRAY_FILTER_USE_BOTH);

        return $properties;
    }

    public function getAttributes(): ?AttributeCollectionInterface
    {
        $attributes = $this->getProperties();
        $attributes = array_filter($attributes, function ($propertyName) {
            return false === $this->isRelationship($propertyName);
        }, ARRAY_FILTER_USE_KEY);

        $finalAttributes = [];
        foreach ($attributes as $attributeName => $attributeValue) {
            $attributeName = $this->modifyAttributeName($attributeName);
            $finalAttributes[] = new Attribute($attributeName, $attributeValue);
        }

        return new AttributeCollection($finalAttributes);
    }

    public function getRelationships(): ?RelationshipCollectionInterface
    {
        $relationships = $this->getProperties();
        $relationships = array_filter($relationships, function ($propertyName) {
            return true === $this->isRelationship($propertyName);
        }, ARRAY_FILTER_USE_KEY);

        $finalRelationships = [];
        foreach ($relationships as $relationshipName => $value) {
            if (true === is_array($value)) {
                Assertion::allString($value);
            } elseif (null !== $value) {
                Assertion::string($value);
            }

            $isToMany = substr($relationshipName, -3) === 'Ids';
            $isToOne = substr($relationshipName, -2) === 'Id';

            if ($isToMany === $isToOne) {
                throw new \LogicException('Invalid relationship check result; both isToMany and isToOne are the same');
            }

            $targetResourceType = str_replace(['Ids', 'Id'], '', $relationshipName);
            $relationshipName = $this->modifyRelationshipName($targetResourceType);

            $factory = new RelationshipDataFactory();
            $data = $factory->make($targetResourceType, $isToMany, $value);
            $finalRelationships[] = new Relationship($relationshipName, null, $data);
        }

        return new RelationshipCollection($finalRelationships);
    }

    public function getSelfUrl(): ?LinkInterface
    {
        return null;
    }

    public function getMeta(): ?MetaInterface
    {
        return null;
    }
}
