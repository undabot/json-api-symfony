<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Symfony\Component\HttpFoundation\Response;

class BadRequestJsonApiResponse extends AbstractErrorJsonApiResponse
{
    public function __construct(string $message = null, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_BAD_REQUEST, $headers);
        $this->message = $message;
    }

    public function getErrorTitle(): string
    {
        return 'Bad Request';
    }
}
