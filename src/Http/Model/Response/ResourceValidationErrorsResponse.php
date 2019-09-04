<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\Validator\ConstraintViolation;
use Undabot\JsonApi\Model\Error\ErrorCollection;
use Undabot\SymfonyJsonApi\Model\Error\ValidationViolationError;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Exception\ModelInvalid;

final class ResourceValidationErrorsResponse
{
    /** @var ErrorCollection */
    private $errorCollection;

    public static function fromException(ModelInvalid $exception): self
    {
        $errors = [];

        /** @var ConstraintViolation $violation */
        foreach ($exception->getViolations() as $violation) {
            $errors[] = new ValidationViolationError($violation);
        }

        return new self(new ErrorCollection($errors));
    }

    public function __construct(ErrorCollection $errorCollection)
    {
        $this->errorCollection = $errorCollection;
    }

    public function getErrorCollection(): ErrorCollection
    {
        return $this->errorCollection;
    }
}
