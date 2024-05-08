<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\TypeHelper;

class AttributeSchema implements Schema
{
    private string $name;

    private string $type;

    private bool $nullable;

    private ?string $description;

    private ?string $format;

    private ?string $example;

    public function __construct(
        string $name,
        string $type,
        bool $nullable,
        ?string $description,
        ?string $format,
        ?string $example
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->description = $description;
        $this->format = $format;
        $this->example = $example;
    }

    public function toOpenApi(): array
    {
        /** @todo add support for float and double formats */
        $schema = [
            'title' => $this->name,
            'type' => TypeHelper::resolve($this->type),
            'nullable' => $this->nullable,
        ];

        if (null !== $this->description) {
            $schema['description'] = $this->description;
        }

        if (null !== $this->example) {
            $schema['example'] = $this->example;
        }

        if (null !== $this->format) {
            $schema['format'] = $this->format;
        }

        return $schema;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
