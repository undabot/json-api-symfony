<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Endpoint;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Endpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Response;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Response\CollectionResponse;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Filter\FilterSetQueryParam;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Query\IncludeQueryParam;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ReadSchema;

class ResourceCollectionEndpoint implements Endpoint
{
    /** @var Response[] */
    private $responses;

    /**
     * @param mixed[] $filters
     * @param mixed[] $sorts
     * @param mixed[] $includes
     * @param mixed[] $fields
     * @param mixed[] $errorResponses
     */
    public function __construct(
        private readonly ReadSchema $schema,
        private readonly string $path,
        private readonly array $filters = [],
        private readonly array $sorts = [],
        private readonly array $includes = [],
        private readonly array $fields = [],
        private readonly ?Schema $pagination = null,
        array $errorResponses = []
    ) {
        $this->responses = array_merge(
            [
                new CollectionResponse($this->schema, $this->includes),
            ],
            $errorResponses
        );
    }

    public function getMethod(): string
    {
        return self::METHOD_GET;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getParams(): array
    {
        $queryParams = [];

        if (0 !== \count($this->includes)) {
            $include = new IncludeQueryParam(array_keys($this->includes));
            $queryParams[] = $include->toOpenApi();
        }

        if (null !== $this->fields) {
            // @todo Add support for sparse fields
        }

        if (false === empty($this->filters)) {
            $filterSet = new FilterSetQueryParam('filter', $this->filters);
            $queryParams[] = $filterSet->toOpenApi();
        }

        if (null !== $this->pagination) {
            $queryParams[] = $this->pagination->toOpenApi();
        }

        return $queryParams;
    }

    public function toOpenApi(): array
    {
        $responses = [];

        /** @var Response $response */
        foreach ($this->responses as $response) {
            $responses[$response->getStatusCode()] = $response->toOpenApi();
        }

        return [
            'summary' => 'List ' . $this->schema->getResourceType(),
            'operationId' => 'list' . ucwords($this->schema->getResourceType()) . 'Collection',
            'description' => 'List collection of ' . $this->schema->getResourceType(),
            'parameters' => $this->getParams(),
            'tags' => [$this->schema->getResourceType()],
            'responses' => $responses,
        ];
    }
}
