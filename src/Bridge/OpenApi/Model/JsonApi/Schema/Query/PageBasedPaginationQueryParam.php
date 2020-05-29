<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Query;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class PageBasedPaginationQueryParam implements Schema
{
    public function toOpenApi(): array
    {
        return [
            'name' => 'page',
            'in' => 'query',
            'style' => 'deepObject',
            'explode' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'number' => [
                        'type' => 'integer',
                        'description' => 'Page number',
                        'example' => 1,
                    ],
                    'size' => [
                        'type' => 'integer',
                        'description' => 'Page size',
                        'example' => 20,
                    ],
                ],
            ],
        ];
    }
}
