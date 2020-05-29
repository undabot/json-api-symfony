<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Response;

class ErrorResponse implements Response
{
    public function getStatusCode(): int
    {
        // TODO: Implement getStatusCode() method.
    }

    public function getContentType(): string
    {
        // TODO: Implement getContentType() method.
    }

    public function getDescription(): ?string
    {
        // TODO: Implement getDescription() method.
    }

    public function toOpenApi(): array
    {
        // TODO: Implement toOpenApi() method.
    }
}
