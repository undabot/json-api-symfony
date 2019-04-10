<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Symfony\Component\HttpFoundation\Response;

class NotAcceptableJsonApiResponse extends AbstractErrorJsonApiResponse
{
    public function __construct(string $message = null, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_NOT_ACCEPTABLE, $headers);
        $this->message = $message;
    }

    public function getErrorTitle(): string
    {
        return 'Not Acceptable';
    }
}
