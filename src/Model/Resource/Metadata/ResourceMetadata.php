<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Metadata;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint as JsonApiConstraint;

class ResourceMetadata
{
    /** @var array */
    private $resourceConstraints;

    /** @var Collection */
    private $attributesMetadata;

    /** @var Collection */
    private $relationshipsMetadata;

    /** @var string|null */
    private $type;

    public function __construct(
        array $resourceConstraints,
        array $attributesMetadata,
        array $relationshipsMetadata
    ) {
        Assertion::allIsInstanceOf($resourceConstraints, Constraint::class);
        Assertion::allIsInstanceOf($attributesMetadata, AttributeMetadata::class);
        Assertion::allIsInstanceOf($relationshipsMetadata, RelationshipMetadata::class);

        $this->resourceConstraints = $resourceConstraints;
        $this->attributesMetadata = new ArrayCollection($attributesMetadata);
        $this->relationshipsMetadata = new ArrayCollection($relationshipsMetadata);

        /** @var JsonApiConstraint\ResourceType[] $resourceTypeConstraints */
        $resourceTypeConstraints = array_filter($resourceConstraints, function (Constraint $constraint) {
            return $constraint instanceof JsonApiConstraint\ResourceType;
        });

        Assertion::maxCount(
            $resourceTypeConstraints,
            1,
            'More than 1 ResourceType constraint found in the resource constraints (metadata)'
        );

        if (count($resourceTypeConstraints) === 1) {
            $this->type = array_values($resourceTypeConstraints)[0]->type;
        }
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return Constraint[]
     */
    public function getResourceConstraints(): array
    {
        return $this->resourceConstraints;
    }

    public function getAttributesConstraints(): array
    {
        $constraints = [];
        /** @var AttributeMetadata $attributeMetadatum */
        foreach ($this->attributesMetadata as $attributeMetadatum) {
            $constraints[$attributeMetadatum->getName()] = $attributeMetadatum->getConstraints();
        }

        return $constraints;
    }

    /**
     * Does the $constraint operates on object? I.e. does the constraint validator needs full ResourceInterface
     * object to perform the validation, or simply the value (string or string[]) is enough?
     *
     * @param Constraint $constraint
     * @return bool
     */
    private function relationshipConstraintWorksOnObject(Constraint $constraint): bool
    {
        return $constraint instanceof JsonApiConstraint\ResourceType;
    }

    /**
     * Returns map of relationship names and constraints that operate on RelationshipInterface objects
     *
     * @return array
     */
    public function getRelationshipsObjectConstraints(): array
    {
        $constraints = [];
        /** @var RelationshipMetadata $relationshipMetadatum */
        foreach ($this->relationshipsMetadata as $relationshipMetadatum) {
            $objectConstraints = array_filter($relationshipMetadatum->getConstraints(),
                function (Constraint $constraint) {
                    return true === $this->relationshipConstraintWorksOnObject($constraint);
                });

            $constraints[$relationshipMetadatum->getName()] = array_values($objectConstraints);
        }

        return $constraints;
    }

    /**
     * Returns map of relationship names and constraints that operate on raw relationship values (string or string[])
     *
     * @return array
     */
    public function getRelationshipsValueConstraints(): array
    {
        $constraints = [];

        /** @var RelationshipMetadata $relationshipMetadatum */
        foreach ($this->relationshipsMetadata as $relationshipMetadatum) {
            $valueConstraints = array_filter($relationshipMetadatum->getConstraints(),
                function (Constraint $constraint) {
                    return false === $this->relationshipConstraintWorksOnObject($constraint);
                });

            $constraints[$relationshipMetadatum->getName()] = array_values($valueConstraints);
        }

        return $constraints;
    }

    /**
     * @return Collection|AttributeMetadata[]
     */
    public function getAttributesMetadata(): Collection
    {
        return $this->attributesMetadata;
    }

    public function getAttributeMetadata(string $name): ?AttributeMetadata
    {
        $metadata = $this->attributesMetadata
            ->filter(function (AttributeMetadata $attributeMetadata) use ($name) {
                return $attributeMetadata->getName() === $name;
            })
            ->first();

        if (false === $metadata) {
            return null;
        }

        return $metadata;
    }

    /**
     * @return Collection|RelationshipMetadata[]
     */
    public function getRelationshipsMetadata(): Collection
    {
        return $this->relationshipsMetadata;
    }

    public function getRelationshipMetadata(string $name): ?RelationshipMetadata
    {
        $metadata = $this->relationshipsMetadata
            ->filter(function (RelationshipMetadata $relationshipMetadata) use ($name) {
                return $relationshipMetadata->getName() === $name;
            })
            ->first();

        if (false === $metadata) {
            return null;
        }

        return $metadata;
    }

    public function getAttributesAliasMap(): array
    {
        $map = [];

        $this->attributesMetadata
            ->filter(function (AttributeMetadata $attributeMetadata) {
                return $attributeMetadata->getName() !== $attributeMetadata->getPropertyPath();
            })
            ->map(function (AttributeMetadata $attributeMetadata) use (&$map) {
                $map[$attributeMetadata->getName()] = $attributeMetadata->getPropertyPath();

                return $attributeMetadata;
            });

        return $map;
    }

    public function getRelationshipsAliasMap(): array
    {
        $map = [];

        $this->relationshipsMetadata
            ->filter(function (RelationshipMetadata $relationshipMetadata) {
                return $relationshipMetadata->getName() !== $relationshipMetadata->getPropertyPath();
            })
            ->map(function (RelationshipMetadata $relationshipMetadata) use (&$map) {
                $map[$relationshipMetadata->getName()] = $relationshipMetadata->getPropertyPath();

                return $relationshipMetadata;
            });

        return $map;
    }
}
