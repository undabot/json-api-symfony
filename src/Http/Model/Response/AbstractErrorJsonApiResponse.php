<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\JsonApi\Model\Error\Error;
use Undabot\JsonApi\Model\Error\ErrorCollection;
use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;

abstract class AbstractErrorJsonApiResponse extends Response implements JsonApiErrorResponseInterface
{
    /** @var string|null */
    protected $message;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    abstract public function getErrorTitle(): string;

    public function getErrorCollection(): ?ErrorCollectionInterface
    {
        if (null !== $this->message) {
            return new ErrorCollection([
                new Error(
                    null,
                    null,
                    (string) $this->getStatusCode(),
                    (string) $this->getStatusCode(),
                    $this->getErrorTitle(),
                    $this->message
                ),
            ]);
        }

        return null;
    }
}
