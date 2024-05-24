<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class QueryParam implements Schema
{
    public function __construct(private string $name, private bool $required, private string $description, private Schema $schema) {}

    public function toOpenApi(): array
    {
        return [
            'in' => 'query',
            'name' => $this->name,
            'required' => $this->required,
            'description' => $this->description,
            'schema' => $this->schema->toOpenApi(),
        ];
    }
}
