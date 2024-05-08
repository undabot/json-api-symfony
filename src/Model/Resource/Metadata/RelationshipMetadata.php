<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Metadata;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Relationship;

class RelationshipMetadata
{
    protected bool $isToMany;

    private string $name;

    private string $relatedResourceType;

    private string $propertyPath;

    private array $constraints;

    private Relationship $relationshipAnnotation;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct(
        string $name,
        string $relatedResourceType,
        string $propertyPath,
        array $constraints,
        bool $isToMany,
        Relationship $relationshipAnnotation
    ) {
        Assertion::allIsInstanceOf($constraints, Constraint::class);

        $this->name = $name;
        $this->relatedResourceType = $relatedResourceType;
        $this->propertyPath = $propertyPath;
        $this->constraints = $constraints;
        $this->isToMany = $isToMany;
        $this->relationshipAnnotation = $relationshipAnnotation;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function getRelatedResourceType(): string
    {
        return $this->relatedResourceType;
    }

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }

    public function isToMany(): bool
    {
        return $this->isToMany;
    }

    public function getRelationshipAnnotation(): Relationship
    {
        return $this->relationshipAnnotation;
    }
}
