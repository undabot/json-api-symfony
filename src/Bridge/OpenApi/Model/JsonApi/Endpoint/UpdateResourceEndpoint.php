<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Endpoint;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Endpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Response;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\SchemaReference;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Requests\UpdateResourceRequest;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ReadSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\UpdateSchema;

class UpdateResourceEndpoint implements Endpoint
{
    /** @var UpdateSchema */
    private $resourceUpdateSchema;

    /** @var string */
    private $path;

    /** @var Response[] */
    private $responses;

    /**
     * @param Response[] $errorResponses
     */
    public function __construct(
        ReadSchema $resourceReadSchema,
        UpdateSchema $resourceUpdateSchema,
        string $path,
        array $errorResponses = []
    ) {
        $this->resourceUpdateSchema = $resourceUpdateSchema;
        $this->path = $path;

        $this->responses = array_merge(
            [new ResourceUpdatedResponse($resourceReadSchema)],
            $errorResponses
        );
    }

    public function getMethod(): string
    {
        return self::METHOD_PATCH;
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

        $request = new UpdateResourceRequest(
            $this->resourceUpdateSchema->getResourceType(),
            $this->resourceUpdateSchema
        );

        return [
            'summary' => 'Update ' . $this->resourceUpdateSchema->getResourceType(),
            'operationId' => 'update' . ucwords($this->resourceUpdateSchema->getResourceType()),
            'description' => 'Update ' . $this->resourceUpdateSchema->getResourceType() . ' resource',
            'tags' => [$this->resourceUpdateSchema->getResourceType()],
            'responses' => $responses,
            'requestBody' => [
                'description' => $this->resourceUpdateSchema->getResourceType() . ' update model',
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

    /** @todo add support for inclusion and sparse fieldset */
    public function getParams(): ?array
    {
        return null;
    }
}
