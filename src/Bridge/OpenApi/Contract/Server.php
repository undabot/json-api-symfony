<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

interface Server extends Schema
{
    public function toOpenApi(): array;
}
