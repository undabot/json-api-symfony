<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class StringSchema implements Schema
{
    public function __construct(private readonly ?string $example = null, private readonly ?string $description = null, private readonly ?string $format = null) {}

    public function toOpenApi(): array
    {
        $schema = [
            'type' => 'string',
        ];

        if (null !== $this->format) {
            $schema['format'] = $this->format;
        }

        if (null !== $this->example) {
            $schema['example'] = $this->example;
        }

        if (null !== $this->description) {
            $schema['description'] = $this->description;
        }

        return $schema;
    }
}
