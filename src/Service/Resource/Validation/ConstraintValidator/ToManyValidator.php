<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToMany;

class ToManyValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param ToMany $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        Assertion::isInstanceOf($constraint, ToMany::class);

        if (false === is_array($value)) {
            $this->context->buildViolation(ToMany::MESSAGE)
                ->addViolation();
        }
    }
}
