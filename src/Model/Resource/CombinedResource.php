<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource;

use InvalidArgumentException;
use Undabot\JsonApi\Definition\Model\Link\LinkInterface;
use Undabot\JsonApi\Definition\Model\Meta\MetaInterface;
use Undabot\JsonApi\Definition\Model\Resource\Attribute\AttributeCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\Attribute\AttributeInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\RelationshipCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Implementation\Model\Resource\Relationship\RelationshipCollection;
use Undabot\SymfonyJsonApi\Model\Resource\Exception\ResourceIdValueMismatch;
use Undabot\SymfonyJsonApi\Model\Resource\Exception\ResourceTypeValueMismatch;

class CombinedResource implements ResourceInterface
{
    /** @var ResourceInterface */
    private $baseResource;

    /** @var ResourceInterface */
    private $updateResource;

    public function __construct(ResourceInterface $baseResource, ResourceInterface $updateResource)
    {
        if ($baseResource->getId() !== $updateResource->getId()) {
            throw ResourceIdValueMismatch::whenUpdatingResource(
                $baseResource->getType(),
                $baseResource->getId(),
                $updateResource->getId()
            );
        }

        if ($baseResource->getType() !== $updateResource->getType()) {
            throw ResourceTypeValueMismatch::whenUpdatingResource(
                $baseResource->getType(),
                $updateResource->getType()
            );
        }

        $flatBaseResource = new FlatResource($baseResource);
        $flatUpdateResource = new FlatResource($updateResource);

        $unsupportedAttributes = array_diff(
            array_keys($flatUpdateResource->getAttributes()),
            array_keys($flatBaseResource->getAttributes())
        );

        if (0 !== \count($unsupportedAttributes)) {
            $message = sprintf('Unsupported attributes found: `%s`', implode(', ', $unsupportedAttributes));

            throw new InvalidArgumentException($message);
        }

        $unsupportedRelationships = array_diff(
            array_keys($flatUpdateResource->getRelationships()),
            array_keys($flatBaseResource->getRelationships())
        );
        if (0 !== \count($unsupportedRelationships)) {
            $message = sprintf('Unsupported relationships found: `%s`', implode(', ', $unsupportedRelationships));

            throw new InvalidArgumentException($message);
        }

        $this->baseResource = $baseResource;
        $this->updateResource = $updateResource;
    }

    public function getId(): string
    {
        return $this->baseResource->getId();
    }

    public function getType(): string
    {
        return $this->baseResource->getType();
    }

    public function getSelfUrl(): ?LinkInterface
    {
        return $this->baseResource->getSelfUrl();
    }

    public function getMeta(): ?MetaInterface
    {
        return $this->baseResource->getMeta();
    }

    public function getAttributes(): ?AttributeCollectionInterface
    {
        $primaryAttributes = $this->baseResource->getAttributes();
        if (null === $primaryAttributes) {
            return null;
        }

        $secondaryAttributes = $this->updateResource->getAttributes();
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
        $primaryRelationships = $this->baseResource->getRelationships();
        if (null === $primaryRelationships) {
            return null;
        }

        $secondaryRelationships = $this->updateResource->getRelationships();
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
