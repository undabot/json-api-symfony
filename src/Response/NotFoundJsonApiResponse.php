<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\JsonApi\Model\Error\Error;
use Undabot\JsonApi\Model\Error\ErrorCollection;
use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;

/**
 * https://jsonapi.org/format/#crud-creating-responses-404
 */
class NotFoundJsonApiResponse extends AbstractErrorJsonApiResponse
{
    /** @var string */
    protected $message;

    public function __construct(string $message, array $headers = [])
    {
        $this->message = $message;
        parent::__construct(null, Response::HTTP_NOT_FOUND, $headers);
    }

    public function getErrorCollection(): ?ErrorCollectionInterface
    {
        if (null !== $this->message) {
            return new ErrorCollection([
                new Error(null, null, '404', '404', $this->getErrorTitle(), $this->message),
            ]);
        }

        return null;
    }

    public function getErrorTitle(): string
    {
        return 'Requested resource not found';
    }
}
