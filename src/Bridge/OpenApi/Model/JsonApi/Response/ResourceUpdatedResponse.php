<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

class ResourceUpdatedResponse extends ResourceResponse
{
    #[\Override]
    public function getStatusCode(): int
    {
        return 200;
    }

    #[\Override]
    public function getDescription(): ?string
    {
        return 'Successful response after updating JSON:API resource';
    }
}
