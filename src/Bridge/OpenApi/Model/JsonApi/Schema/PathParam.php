<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class PathParam implements Schema
{
    public function __construct(private readonly string $name, private readonly bool $required, private readonly string $description, private readonly Schema $schema) {}

    public function toOpenApi(): array
    {
        return [
            'in' => 'path',
            'name' => $this->name,
            'required' => $this->required,
            'description' => $this->description,
            'schema' => $this->schema->toOpenApi(),
        ];
    }
}
