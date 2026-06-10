<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Metadata;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Relationship;

class RelationshipMetadata
{
    /** @var bool */
    protected $isToMany;

    /** @var array */
    private $constraints;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct(
        private readonly string $name,
        private readonly string $relatedResourceType,
        private readonly string $propertyPath,
        array $constraints,
        bool $isToMany,
        private readonly Relationship $relationshipAnnotation
    ) {
        Assertion::allIsInstanceOf($constraints, Constraint::class);
        $this->constraints = $constraints;
        $this->isToMany = $isToMany;
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
