<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\Api;

interface OpenApiDefinition
{
    public function getApi(): Api;

    public function getFileName(): string;
}
