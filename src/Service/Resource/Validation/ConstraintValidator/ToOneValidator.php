<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ToOne;

class ToOneValidator extends ConstraintValidator
{
    /**
     * @param ToOne $constraint
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assertion::isInstanceOf($constraint, ToOne::class);

        if (false === \is_string($value) && null !== $value) {
            $this->context->buildViolation(ToOne::MESSAGE)
                ->addViolation();
        }
    }
}
