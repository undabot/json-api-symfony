<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Metadata;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;

class RelationshipMetadata
{
    /** @var bool */
    protected $isToMany;
    /** @var string */
    private $name;

    /** @var string */
    private $relatedResourceType;

    /** @var string */
    private $propertyPath;

    /** @var array */
    private $constraints;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct(
        string $name,
        string $relatedResourceType,
        string $propertyPath,
        array $constraints,
        bool $isToMany
    ) {
        Assertion::allIsInstanceOf($constraints, Constraint::class);

        $this->name = $name;
        $this->relatedResourceType = $relatedResourceType;
        $this->propertyPath = $propertyPath;
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
}
