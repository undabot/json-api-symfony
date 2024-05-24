<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Response;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\SchemaReference;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ReadSchema;

class ResourceResponse implements Response
{
    /** @var array<string, ReadSchema> */
    private array $includes;

    /**
     * @param array<string, ReadSchema> $includes
     */
    public function __construct(private ReadSchema $readSchema, array $includes = [])
    {
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
        return 'Successful response for getting the resource instance';
    }

    public function toOpenApi(): array
    {
        $responseContentSchema = [
            'schema' => [
                'type' => 'object',
                'required' => ['data'],
                'properties' => [
                    'data' => [
                        '$ref' => SchemaReference::ref($this->readSchema->getName()),
                    ],
                ],
            ],
        ];

        if (false === empty($this->includes)) {
            $includedSchema = new IncludedSchema($this->includes);
            $responseContentSchema['schema']['properties']['included'] = $includedSchema->toOpenApi();
        }

        return [
            'description' => $this->getDescription(),
            'content' => [
                $this->getContentType() => $responseContentSchema,
            ],
        ];
    }
}
