<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Validation\ConstraintValidator;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Undabot\SymfonyJsonApi\Resource\Validation\Constraint\ToOne;

class ToOneValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param ToOne $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        Assertion::isInstanceOf($constraint, ToOne::class);

        if (false === is_string($value) && null !== $value) {
            $this->context->buildViolation(ToOne::MESSAGE)
                ->addViolation();
        }
    }
}
