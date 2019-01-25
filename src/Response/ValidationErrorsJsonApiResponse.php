<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Undabot\JsonApi\Model\Error\ErrorCollection;
use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;
use Undabot\SymfonyJsonApi\Model\Error\ValidationViolationError;

class ValidationErrorsJsonApiResponse extends Response implements JsonApiErrorResponseInterface
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $violations;

    /** @var ErrorCollectionInterface|null */
    private $errorCollection;

    public function __construct(ConstraintViolationListInterface $violations, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_UNPROCESSABLE_ENTITY, $headers);

        $this->violations = $violations;
        $this->errorCollection = $this->makeErrorCollectionFromViolations($violations);
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    private function makeErrorCollectionFromViolations(
        ConstraintViolationListInterface $violations
    ): ?ErrorCollectionInterface {
        if (0 === $violations->count()) {
            return null;
        }

        $errors = [];
        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $errors[] = new ValidationViolationError($violation);
        }

        return new ErrorCollection($errors);
    }

    public function getErrorCollection(): ?ErrorCollectionInterface
    {
        return $this->errorCollection;
    }
}
