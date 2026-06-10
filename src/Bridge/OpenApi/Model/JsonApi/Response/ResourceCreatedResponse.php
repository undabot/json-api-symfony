<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

class ResourceCreatedResponse extends ResourceResponse
{
    #[\Override]
    public function getStatusCode(): int
    {
        return 201;
    }

    #[\Override]
    public function getDescription(): ?string
    {
        return 'Successful response after creating JSON:API resource';
    }
}
