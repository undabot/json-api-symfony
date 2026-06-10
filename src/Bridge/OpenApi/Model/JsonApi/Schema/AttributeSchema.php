<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\TypeHelper;

class AttributeSchema implements Schema
{
    public function __construct(private readonly string $name, private readonly string $type, private readonly bool $nullable, private readonly ?string $description, private readonly ?string $format, private readonly mixed $example) {}

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
