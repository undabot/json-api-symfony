<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator\ToManyValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ToMany extends Constraint
{
    public const MESSAGE = 'This value must be an array.';

    #[\Override]
    public function validatedBy(): string
    {
        return ToManyValidator::class;
    }
}
