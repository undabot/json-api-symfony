<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Resource\Validation\ConstraintValidator\ToOneValidator;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ToOne extends Constraint
{
    public const MESSAGE = 'This value must string or null.';

    public function validatedBy()
    {
        return ToOneValidator::class;
    }
}
