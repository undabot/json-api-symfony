<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Metadata;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Attribute;

class AttributeMetadata
{
    /** @var string */
    private $name;

    /** @var string */
    private $propertyPath;

    /** @var array */
    private $constraints;

    /** @var Attribute */
    private $attributeAnnotation;

    /**
     * @param Constraint[] $constraints
     */
    public function __construct(
        string $name,
        string $propertyPath,
        array $constraints,
        Attribute $attributeAnnotation
    ) {
        Assertion::allIsInstanceOf($constraints, Constraint::class);

        $this->name = $name;
        $this->propertyPath = $propertyPath;
        $this->constraints = $constraints;
        $this->attributeAnnotation = $attributeAnnotation;
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
