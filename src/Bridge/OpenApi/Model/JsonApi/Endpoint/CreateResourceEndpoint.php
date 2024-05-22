<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Endpoint;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Endpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Response;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\SchemaReference;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Requests\CreateResourceRequest;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\CreateSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ReadSchema;

class CreateResourceEndpoint implements Endpoint
{
    /** @var Response[] */
    private array $responses;

    /**
     * @param Response[] $errorResponses
     */
    public function __construct(
        private ReadSchema $readSchema,
        private CreateSchema $createSchema,
        private string $path,
        array $errorResponses = []
    ) {
        $this->responses = array_merge(
            [
                new ResourceCreatedResponse($this->readSchema),
            ],
            $errorResponses
        );
    }

    public function getMethod(): string
    {
        return self::METHOD_POST;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function toOpenApi(): array
    {
        $responses = [];

        /** @var Response $response */
        foreach ($this->responses as $response) {
            $responses[$response->getStatusCode()] = $response->toOpenApi();
        }

        $request = new CreateResourceRequest(
            $this->createSchema->getResourceType(),
            $this->createSchema
        );

        return [
            'summary' => 'Create ' . $this->readSchema->getResourceType(),
            'operationId' => 'create' . ucwords($this->readSchema->getResourceType()),
            'description' => 'Create ' . $this->readSchema->getResourceType() . ' resource',
            'tags' => [$this->readSchema->getResourceType()],
            'responses' => $responses,
            'requestBody' => [
                'description' => $this->readSchema->getResourceType() . ' create model',
                'required' => true,
                'content' => [
                    $request->getContentType() => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['data'],
                            'properties' => [
                                'data' => [
                                    '$ref' => SchemaReference::ref($request->getSchemaReference()),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParams(): ?array
    {
        return null;
    }
}
