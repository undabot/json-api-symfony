<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * https://jsonapi.org/format/#crud-creating-responses-404
 */
class NotFoundJsonApiResponse extends Response
{
    public function __construct(array $headers = [])
    {
        parent::__construct(null, Response::HTTP_NOT_FOUND, $headers);
    }
}
