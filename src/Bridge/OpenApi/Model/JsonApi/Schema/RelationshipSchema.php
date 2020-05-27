<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\SchemaReference;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\IdentifierSchema;

class RelationshipSchema implements Schema
{
    /** @var string */
    private $name;

    /** @var null|string */
    private $description;

    /** @var bool */
    private $nullable;

    /** @var string */
    private $targetResourceType;

    /** @var bool */
    private $isToMany;

    public function __construct(
        string $name,
        ?string $description,
        bool $nullable,
        string $targetResourceType,
        bool $isToMany
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->nullable = $nullable;
        $this->targetResourceType = $targetResourceType;
        $this->isToMany = $isToMany;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function toOpenApi(): array
    {
        $ref = (new IdentifierSchema($this->targetResourceType))->getName();
        $ref = SchemaReference::ref($ref);

        if (false === $this->isToMany) {
            return [
                'type' => 'object',
                'required' => ['data'],
                'nullable' => false,
                'properties' => [
                    'data' => [
                        '$ref' => $ref,
                    ],
                ],
            ];
        }

        return [
            'type' => 'object',
            'required' => ['data'],
            'nullable' => false,
            'properties' => [
                'data' => [
                    'type' => 'array',
                    'items' => [
                        '$ref' => $ref,
                    ],
                ],
            ],
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }
}
