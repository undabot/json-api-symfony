<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Exception\Request;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ResourceValidationException extends JsonApiRequestException
{
    /** @var ConstraintViolationListInterface */
    private $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        parent::__construct();
        $this->violations = $violations;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
