<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestContentTypeHeaderException;

class UnsupportedMediaTypeJsonApiResponse extends AbstractErrorJsonApiResponse
{
    public function __construct(InvalidRequestContentTypeHeaderException $exception, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $headers);
        $this->message = $exception->getMessage();
    }
}
