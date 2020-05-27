<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

interface Schema
{
    /** @return mixed[] */
    public function toOpenApi(): array;
}
