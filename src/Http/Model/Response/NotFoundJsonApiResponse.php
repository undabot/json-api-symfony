<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\JsonApi\Model\Error\Error;
use Undabot\JsonApi\Model\Error\ErrorCollection;
use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;

/**
 * https://jsonapi.org/format/#crud-creating-responses-404
 */
class NotFoundJsonApiResponse extends AbstractErrorJsonApiResponse
{
    /** @var string|null */
    protected $message;

    public function __construct(string $message = null, array $headers = [])
    {
        $this->message = $message;
        parent::__construct(null, Response::HTTP_NOT_FOUND, $headers);
    }

    public function getErrorCollection(): ?ErrorCollectionInterface
    {
        return new ErrorCollection([
                new Error(null, null, '404', '404', $this->getErrorTitle(), $this->message),
            ]);
    }

    public function getErrorTitle(): string
    {
        return 'Requested resource not found';
    }
}
