<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Query;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class IncludeQueryParam implements Schema
{
    private ?string $description;

    /** @var string[] */
    private array $includes;

    /** @var null|string[] */
    private ?array $default;

    /**
     * @param string[]      $includes
     * @param null|string[] $default
     */
    public function __construct(array $includes, ?string $description = null, ?array $default = null)
    {
        $this->includes = $includes;
        $this->description = $description;
        $this->default = $default;

        if (null === $description) {
            $this->description = 'Relationships to be included. Available: ' . implode(',', $includes);
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
