<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class UuidSchema implements Schema
{
    public function toOpenApi(): array
    {
        return [
            'type' => 'string',
            'format' => 'uuid',
            'nullable' => false,
            'example' => 'd290f1ee-6c54-4b01-90e6-d701748f0851',
        ];
    }
}
