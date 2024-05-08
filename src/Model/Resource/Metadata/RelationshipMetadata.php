<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Metadata;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Relationship;

class RelationshipMetadata
{
    private array $constraints;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct(
        private string $name,
        private string $relatedResourceType,
        private string $propertyPath,
        array $constraints,
        protected bool $isToMany,
        private Relationship $relationshipAnnotation
    ) {
        Assertion::allIsInstanceOf($constraints, Constraint::class);
        $this->constraints = $constraints;
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
