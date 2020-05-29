<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Response;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\SchemaReference;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ReadSchema;

class CollectionResponse implements Response
{
    /** @var ReadSchema */
    private $schema;

    /** @var array<string, ReadSchema> */
    private $includes;

    /**
     * @param array<string, ReadSchema> $includes
     */
    public function __construct(ReadSchema $schema, array $includes)
    {
        $this->schema = $schema;
        Assertion::allIsInstanceOf($includes, ReadSchema::class);
        $this->includes = $includes;
    }

    public function getStatusCode(): int
    {
        return 200;
    }

    public function getContentType(): string
    {
        return 'application/vnd.api+json';
    }

    public function getDescription(): ?string
    {
        return 'Successful response for getting the collection of resources';
    }

    public function toOpenApi(): array
    {
        $responseContentSchema = [
            'schema' => [
                'type' => 'object',
                'required' => ['data'],
                'properties' => [
                    'data' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => SchemaReference::ref($this->schema->getName()),
                        ],
                    ],
                ],
            ],
        ];

        if (false === empty($this->includes)) {
            $includedSchema = new IncludedSchema($this->includes);
            if (false === empty($includedSchema)) {
                $responseContentSchema['schema']['properties']['included'] = $includedSchema->toOpenApi();
            }
        }

        return [
            'description' => $this->getDescription(),
            'content' => [
                $this->getContentType() => $responseContentSchema,
            ],
        ];
    }
}
