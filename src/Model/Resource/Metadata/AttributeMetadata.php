<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Metadata;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Attribute;

class AttributeMetadata
{
    private array $constraints;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct(
        private string $name,
        private string $propertyPath,
        array $constraints,
        private Attribute $attributeAnnotation
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

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }

    public function getAttributeAnnotation(): Attribute
    {
        return $this->attributeAnnotation;
    }
}
