<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * https://jsonapi.org/format/#crud-creating-responses-403
 */
class UnauthorizedJsonApiResponse extends AbstractErrorJsonApiResponse
{
    public function __construct(string $message = null, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_UNAUTHORIZED, $headers);
        $this->message = $message;
    }
}
