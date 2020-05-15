<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator\ResourceTypeValidator;

/**
 * @Annotation
 */
class ResourceType extends Constraint
{
    /** @var string */
    public $type;

    /** @var string */
    public $message = 'Invalid resource type `{{ given }}` given; `{{ expected }}` expected.';

    public static function make(string $type): self
    {
        $resourceType = new self();
        $resourceType->type = $type;

        return $resourceType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function validatedBy()
    {
        return ResourceTypeValidator::class;
    }
}
