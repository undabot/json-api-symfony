<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestContentTypeHeaderException;

class UnsupportedMediaTypeJsonApiResponse extends AbstractErrorJsonApiResponse
{
    public function __construct(InvalidRequestContentTypeHeaderException $exception, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $headers);
        $this->message = $exception->getMessage();
    }

    public function getErrorTitle(): string
    {
        return 'Unsupported Media Type';
    }
}
