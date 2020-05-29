<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

interface ResourceSchema extends NamedSchema
{
    public function getName(): string;

    public function getResourceType(): string;
}
