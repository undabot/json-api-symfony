<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Metadata;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint as JsonApiConstraint;

class ResourceMetadata
{
    private array $resourceConstraints;

    /** @var Collection<int, AttributeMetadata> */
    private $attributesMetadata;

    /**
     * @var Collection<int|string, RelationshipMetadata>
     */
    private $relationshipsMetadata;

    /** @var string */
    private $type;

    /**
     * @param Constraint[]           $resourceConstraints
     * @param AttributeMetadata[]    $attributesMetadata
     * @param RelationshipMetadata[] $relationshipsMetadata
     *
     * @throws AssertionFailedException
     */
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

        $resourceTypeConstraints = array_filter($resourceConstraints, static fn (Constraint $constraint) => $constraint instanceof JsonApiConstraint\ResourceType);

        Assertion::count(
            $resourceTypeConstraints,
            1,
            'Exactly 1 resourceType constraint must be defined in the resource constraints (metadata)'
        );

        $this->type = array_values($resourceTypeConstraints)[0]->type;
    }

    public function getType(): string
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

    /**
     * @return array<string, Constraint[]>
     */
    public function getAttributesConstraints(): array
    {
        $constraints = [];

        foreach ($this->attributesMetadata as $attributeMetadatum) {
            $constraints[$attributeMetadatum->getName()] = $attributeMetadatum->getConstraints();
        }

        return $constraints;
    }

    /**
     * Returns map of relationship names and constraints that operate on RelationshipInterface objects.
     *
     * @return array<string, Constraint[]>
     */
    public function getRelationshipsObjectConstraints(): array
    {
        $constraints = [];

        foreach ($this->relationshipsMetadata as $relationshipMetadatum) {
            $objectConstraints = array_filter(
                $relationshipMetadatum->getConstraints(),
                fn (Constraint $constraint) => true === $this->relationshipConstraintWorksOnObject($constraint)
            );

            $constraints[$relationshipMetadatum->getName()] = array_values($objectConstraints);
        }

        return $constraints;
    }

    /**
     * Returns map of relationship names and constraints that operate on raw relationship values (string or string[]).
     *
     * @return array<string, Constraint[]>
     */
    public function getRelationshipsValueConstraints(): array
    {
        $constraints = [];

        foreach ($this->relationshipsMetadata as $relationshipMetadatum) {
            $valueConstraints = array_filter(
                $relationshipMetadatum->getConstraints(),
                fn (Constraint $constraint) => false === $this->relationshipConstraintWorksOnObject($constraint)
            );

            $constraints[$relationshipMetadatum->getName()] = array_values($valueConstraints);
        }

        return $constraints;
    }

    /**
     * @return AttributeMetadata[]|Collection
     */
    public function getAttributesMetadata(): Collection
    {
        return $this->attributesMetadata;
    }

    public function getAttributeMetadata(string $name): ?AttributeMetadata
    {
        $metadata = $this->attributesMetadata
            ->filter(static fn (AttributeMetadata $attributeMetadata) => $attributeMetadata->getName() === $name)
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
            ->filter(static fn (RelationshipMetadata $relationshipMetadata) => $relationshipMetadata->getName() === $name)
            ->first();

        if (false === $metadata) {
            return null;
        }

        return $metadata;
    }

    /**
     * @return array<string, string>
     */
    public function getAttributesAliasMap(): array
    {
        $map = [];

        $this->attributesMetadata
            ->filter(static fn (AttributeMetadata $attributeMetadata) => $attributeMetadata->getName() !== $attributeMetadata->getPropertyPath())
            ->map(static function (AttributeMetadata $attributeMetadata) use (&$map) {
                $map[$attributeMetadata->getName()] = $attributeMetadata->getPropertyPath();

                return $attributeMetadata;
            });

        return $map;
    }

    /**
     * @return array<string, string>
     */
    public function getRelationshipsAliasMap(): array
    {
        $map = [];

        $this->relationshipsMetadata
            ->filter(static fn (RelationshipMetadata $relationshipMetadata) => $relationshipMetadata->getName() !== $relationshipMetadata->getPropertyPath())
            ->map(static function (RelationshipMetadata $relationshipMetadata) use (&$map) {
                $map[$relationshipMetadata->getName()] = $relationshipMetadata->getPropertyPath();

                return $relationshipMetadata;
            });

        return $map;
    }

    /**
     * Does the $constraint operates on object? I.e. does the constraint validator needs full ResourceInterface
     * object to perform the validation, or simply the value (string or string[]) is enough?
     */
    private function relationshipConstraintWorksOnObject(Constraint $constraint): bool
    {
        return $constraint instanceof JsonApiConstraint\ResourceType;
    }
}
