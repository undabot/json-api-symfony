<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

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

    public function getErrorCollection(): ?ErrorCollectionInterface
    {
        if (null !== $this->message) {
            return new ErrorCollection([
                new Error(null, null, null, null, $this->message),
            ]);
        }

        return null;
    }
}
