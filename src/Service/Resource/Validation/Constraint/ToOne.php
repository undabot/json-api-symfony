<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator\ToOneValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ToOne extends Constraint
{
    public const MESSAGE = 'This value must be string or null.';

    #[\Override]
    public function validatedBy(): string
    {
        return ToOneValidator::class;
    }
}
