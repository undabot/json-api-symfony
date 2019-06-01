<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Model\Metadata;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;

class AttributeMetadata
{
    /** @var string */
    private $name;

    /** @var string */
    private $propertyPath;

    /** @var array */
    private $constraints;

    public function __construct(string $name, string $propertyPath, array $constraints)
    {
        Assertion::allIsInstanceOf($constraints, Constraint::class);

        $this->name = $name;
        $this->propertyPath = $propertyPath;
        $this->constraints = $constraints;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }
}
