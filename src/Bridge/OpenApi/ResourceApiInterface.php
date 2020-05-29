<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\ResourceApiEndpointsFactory;

interface ResourceApiInterface
{
    public function getDefinition(): string;

    public function generateResourceApiEndpoints(): ResourceApiEndpointsFactory;
}
