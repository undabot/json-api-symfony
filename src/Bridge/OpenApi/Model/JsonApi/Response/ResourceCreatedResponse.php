<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

class ResourceCreatedResponse extends ResourceResponse
{
    public function getStatusCode(): int
    {
        return 201;
    }

    public function getDescription(): ?string
    {
        return 'Successful response after creating JSON:API resource';
    }
}
