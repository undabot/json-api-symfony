<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Endpoint;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Endpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Response;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\PathParam;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Query\IncludeQueryParam;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ReadSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\UuidSchema;

class GetResourceEndpoint implements Endpoint
{
    /** @var ReadSchema */
    private $readSchema;

    /** @var string */
    private $path;

    /** @var Response[] */
    private $responses;

    /** @var ReadSchema[] */
    private $includes;

    /** @var null|mixed[] */
    private $fields;

    /**
     * @param ReadSchema[] $includes
     * @param null|mixed[] $fields
     * @param mixed[]      $errorResponses
     */
    public function __construct(
        ReadSchema $readSchema,
        string $path,
        array $includes,
        ?array $fields,
        array $errorResponses = []
    ) {
        Assertion::allIsInstanceOf($includes, ReadSchema::class);
        $this->readSchema = $readSchema;
        $this->path = $path;
        $this->includes = $includes;

        $this->responses = array_merge([
            new ResourceResponse($this->readSchema, $this->includes),
        ], $errorResponses);

        $this->fields = $fields;
    }

    public function getMethod(): string
    {
        return self::METHOD_GET;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getParams(): ?array
    {
        $params = [];

        $idPathParam = new PathParam('id', true, 'Requested resource ID', new UuidSchema());
        $params[] = $idPathParam->toOpenApi();

        if (null !== $this->includes) {
            $include = new IncludeQueryParam(array_keys($this->includes));
            $params[] = $include->toOpenApi();
        }

        if (null !== $this->fields) {
            // @todo Add support for fields
        }

        return $params;
    }

    public function toOpenApi(): array
    {
        $responses = [];

        /** @var Response $response */
        foreach ($this->responses as $response) {
            $responses[$response->getStatusCode()] = $response->toOpenApi();
        }

        return [
            'summary' => 'Get ' . $this->readSchema->getResourceType(),
            'operationId' => 'get' . ucwords($this->readSchema->getResourceType()),
            'description' => 'Get single ' . $this->readSchema->getResourceType() . ' resource',
            'tags' => [$this->readSchema->getResourceType()],
            'parameters' => $this->getParams(),
            'responses' => $responses,
        ];
    }

    public function getPath(): string
    {
        return $this->path . '/{id}';
    }
}
