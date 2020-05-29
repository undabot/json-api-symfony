<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\SchemaReference;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ReadSchema;

final class IncludedSchema implements Schema
{
    /**
     * Key-value pairs of `name` => `ReadSchema` for allowed includes.
     *
     * @var array<string, ReadSchema>
     */
    private $includes;

    /**
     * @param array<string, ReadSchema> $includes
     */
    public function __construct(array $includes)
    {
        Assertion::allIsInstanceOf($includes, ReadSchema::class);
        $this->includes = $includes;
    }

    public function toOpenApi(): array
    {
        $includedSchemas = [];

        /** @var ReadSchema $schema */
        foreach ($this->includes as $schema) {
            $includedSchemas[] = ['$ref' => SchemaReference::ref($schema->getName())];
        }

        if (false === empty($includedSchemas)) {
            return [
                'type' => 'array',
                'items' => [
                    'anyOf' => $includedSchemas,
                ],
            ];
        }

        return [];
    }
}
