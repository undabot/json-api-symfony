<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class PathParam implements Schema
{
    /** @var string */
    private $name;

    /** @var bool */
    private $required;

    /** @var string */
    private $description;

    /** @var Schema */
    private $schema;

    public function __construct(string $name, bool $required, string $description, Schema $schema)
    {
        $this->name = $name;
        $this->required = $required;
        $this->description = $description;
        $this->schema = $schema;
    }

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
