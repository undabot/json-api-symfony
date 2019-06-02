<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource;

use Assert\Assertion;
use Undabot\JsonApi\Model\Link\LinkInterface;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollectionInterface;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeInterface;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollectionInterface;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class CombinedResource implements ResourceInterface
{
    /** @var ResourceInterface */
    private $primaryResource;

    /** @var ResourceInterface */
    private $secondaryResource;

    public function __construct(ResourceInterface $baseResource, ResourceInterface $overlayedResource)
    {
        Assertion::same($baseResource->getId(), $overlayedResource->getId());
        Assertion::same($baseResource->getType(), $overlayedResource->getType());

        $this->primaryResource = $baseResource;
        $this->secondaryResource = $overlayedResource;
    }

    public function getId(): string
    {
        return $this->primaryResource->getId();
    }

    public function getType(): string
    {
        return $this->primaryResource->getType();
    }

    public function getSelfUrl(): ?LinkInterface
    {
        return $this->primaryResource->getSelfUrl();
    }

    public function getMeta(): ?MetaInterface
    {
        return $this->primaryResource->getMeta();
    }

    public function getAttributes(): ?AttributeCollectionInterface
    {
        $primaryAttributes = $this->primaryResource->getAttributes();
        if (null === $primaryAttributes) {
            return null;
        }

        $secondaryAttributes = $this->secondaryResource->getAttributes();
        if (null === $secondaryAttributes) {
            return $primaryAttributes;
        }

        $attributes = [];

        /** @var AttributeInterface $primaryAttribute */
        foreach ($primaryAttributes as $primaryAttribute) {
            $secondaryAttribute = $secondaryAttributes->getAttributeByName($primaryAttribute->getName());
            if (null !== $secondaryAttribute) {
                $attributes[] = $secondaryAttribute;
                continue;
            }

            $attributes[] = $primaryAttribute;
        }

        return new AttributeCollection($attributes);
    }

    public function getRelationships(): ?RelationshipCollectionInterface
    {
        $primaryRelationships = $this->primaryResource->getRelationships();
        if (null === $primaryRelationships) {
            return null;
        }

        $secondaryRelationships = $this->secondaryResource->getRelationships();
        if (null === $secondaryRelationships) {
            return $primaryRelationships;
        }

        $relationships = [];

        /** @var RelationshipInterface $primaryRelationship */
        foreach ($primaryRelationships as $primaryRelationship) {
            $secondaryRelationship = $secondaryRelationships->getRelationshipByName($primaryRelationship->getName());

            if (null !== $secondaryRelationship) {
                $relationships[] = $secondaryRelationship;
                continue;
            }

            $relationships[] = $primaryRelationship;
        }

        return new RelationshipCollection($relationships);
    }
}
