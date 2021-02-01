<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Response;

class ErrorResponse implements Response
{
    public function getStatusCode(): int
    {
        return 500;
    }

    public function getContentType(): string
    {
        return 'application/vnd.api+json';
    }

    public function getDescription(): ?string
    {
        return 'Error response';
    }

    public function toOpenApi(): array
    {
        return [
            'errors' => [
                [
                    'code' => 'Code',
                    'title' => 'Error',
                    'detail' => 'Error',
                ],
            ],
        ];
    }
}
