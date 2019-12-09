<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Query;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class OffsetBasedPaginationQueryParam implements Schema
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
                    'offset' => [
                        'type' => 'integer',
                        'description' => 'Pagination offset (start from)',
                        'example' => 0,
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Page size',
                        'example' => 20,
                    ],
                ],
            ],
        ];
    }
}
