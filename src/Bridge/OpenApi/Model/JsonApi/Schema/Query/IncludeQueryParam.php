<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Query;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class IncludeQueryParam implements Schema
{
    /**
     * @param string[]      $includes
     * @param null|string[] $default
     */
    public function __construct(private array $includes, private ?string $description = null, private ?array $default = null)
    {
        if (null === $this->description) {
            $this->description = 'Relationships to be included. Available: ' . implode(',', $this->includes);
        }
    }

    public function toOpenApi(): array
    {
        $schema = [
            'in' => 'query',
            'name' => 'include',
            'required' => false,
            'description' => $this->description,
            'style' => 'form',
            'explode' => false,
            'schema' => [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                ],
            ],
        ];

        if (false === empty($this->includes)) {
            $schema['schema']['items']['enum'] = $this->includes;
        }

        if (null !== $this->default) {
            $schema['schema']['items']['default'] = $this->default;
        }

        return $schema;
    }
}
