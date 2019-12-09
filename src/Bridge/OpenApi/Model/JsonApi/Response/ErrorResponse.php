<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

use JsonApiOpenApi\Model\OpenApi\ResponseInterface;
use JsonApiOpenApi\Model\OpenApi\SchemaInterface;

class ErrorResponse implements ResponseInterface
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

    public function getResourceSchema(): SchemaInterface
    {
        // TODO: Implement getSchema() method.
    }
}
