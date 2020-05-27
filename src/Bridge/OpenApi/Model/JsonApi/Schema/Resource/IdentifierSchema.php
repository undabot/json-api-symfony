<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\ResourceSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\UuidSchema;

class IdentifierSchema implements ResourceSchema
{
    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return ucwords($this->type) . 'Identifier';
    }

    public function toOpenApi(): array
    {
        $uuidSchema = new UuidSchema();

        return [
            'type' => 'object',
            'required' => [
                'id',
                'type',
            ],
            'properties' => [
                'id' => $uuidSchema->toOpenApi(), // @todo add support for non uuid ids
                'type' => [
                    'nullable' => false,
                    'type' => 'string',
                    'example' => $this->type,
                    'description' => $this->type,
                    'enum' => [$this->type],
                ],
            ],
        ];
    }

    public function getResourceType(): string
    {
        return $this->type;
    }
}
