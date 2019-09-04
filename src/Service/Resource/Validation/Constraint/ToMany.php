<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator\ToManyValidator;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ToMany extends Constraint
{
    public const MESSAGE = 'This value must be an array.';

    public function validatedBy()
    {
        return ToManyValidator::class;
    }
}
