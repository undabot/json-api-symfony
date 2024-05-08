<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class IntegerSchema implements Schema
{
    public function __construct(private ?int $example, private ?string $description) {}

    public function toOpenApi(): array
    {
        $schema = [
            'type' => 'integer',
        ];

        if (null !== $this->example) {
            $schema['example'] = $this->example;
        }

        if (null !== $this->description) {
            $schema['description'] = $this->description;
        }

        return $schema;
    }
}
