<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator\ToOneValidator;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ToOne extends Constraint
{
    public const MESSAGE = 'This value must be string or null.';

    public function validatedBy()
    {
        return ToOneValidator::class;
    }
}
