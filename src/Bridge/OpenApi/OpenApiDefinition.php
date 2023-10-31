<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\Api;

interface OpenApiDefinition
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function getApi(): Api;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getFileName(): string;
}
