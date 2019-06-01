<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * https://jsonapi.org/format/#crud-creating-responses-204
 */
class NoContentJsonApiResponse extends Response implements JsonApiResponseInterface
{
    public function __construct(array $headers = [])
    {
        parent::__construct(null, Response::HTTP_NO_CONTENT, $headers);
    }
}
