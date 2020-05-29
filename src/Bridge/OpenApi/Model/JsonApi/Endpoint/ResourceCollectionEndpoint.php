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
    /** @var ReadSchema */
    private $schema;

    /** @var string */
    private $path;

    /** @var Response[] */
    private $responses;

    /** @var mixed[] */
    private $filters;

    /** @var mixed[] */
    private $includes;

    /** @var mixed[] */
    private $fields;

    /** @var mixed[] */
    private $sorts;

    /** @var null|Schema */
    private $pagination;

    /**
     * @param mixed[] $filters
     * @param mixed[] $sorts
     * @param mixed[] $includes
     * @param mixed[] $fields
     * @param mixed[] $errorResponses
     */
    public function __construct(
        ReadSchema $schema,
        string $path,
        array $filters = [],
        array $sorts = [],
        array $includes = [],
        array $fields = [],
        ?Schema $pagination = null,
        array $errorResponses = []
    ) {
        $this->schema = $schema;
        $this->path = $path;
        $this->includes = $includes;

        $this->responses = array_merge(
            [
                new CollectionResponse($this->schema, $this->includes),
            ],
            $errorResponses
        );

        $this->filters = $filters;
        $this->sorts = $sorts;
        $this->fields = $fields;
        $this->pagination = $pagination;
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
