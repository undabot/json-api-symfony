<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

interface NamedSchema extends Schema
{
    public function getName(): string;
}
