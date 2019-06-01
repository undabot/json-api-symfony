<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * https://jsonapi.org/format/#crud-creating-responses-403
 */
class ForbiddenJsonApiResponse extends AbstractErrorJsonApiResponse
{
    public function __construct(string $message = null, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_FORBIDDEN, $headers);
        $this->message = $message;
    }

    public function getErrorTitle(): string
    {
        return 'Access Denied';
    }
}
