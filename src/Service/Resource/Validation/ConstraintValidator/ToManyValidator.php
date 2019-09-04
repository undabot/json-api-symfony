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
     * @param ToMany $constraint
     * @param mixed  $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assertion::isInstanceOf($constraint, ToMany::class);

        if (false === \is_array($value)) {
            $this->context->buildViolation(ToMany::MESSAGE)
                ->addViolation();
        }
    }
}
