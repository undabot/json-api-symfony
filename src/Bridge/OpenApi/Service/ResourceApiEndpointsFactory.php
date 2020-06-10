<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Service;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Api;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Exception\ResourceApiEndpointsException;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Endpoint\CreateResourceEndpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Endpoint\GetResourceEndpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Endpoint\ResourceCollectionEndpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Endpoint\UpdateResourceEndpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Query\OffsetBasedPaginationQueryParam;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Query\PageBasedPaginationQueryParam;

class ResourceApiEndpointsFactory
{
    /** @var ResourceSchemaFactory */
    private $schemaFactory;

    /** @var string */
    private $resourceClassName;

    /** @var string */
    private $path;

    /** @var bool */
    private $getSingle;

    /** @var bool */
    private $getCollection;

    /** @var bool */
    private $create;

    /** @var bool */
    private $update;

    /** @var bool */
    private $delete;

    /** @var mixed[] */
    private $singleIncludes = [];

    /** @var mixed[] */
    private $singleFields = [];

    /** @var mixed[] */
    private $collectionIncludes = [];

    /** @var mixed[] */
    private $collectionFields = [];

    /** @var mixed[] */
    private $collectionFilters = [];

    /** @var mixed[] */
    private $collectionSorts = [];

    /** @var null|Schema */
    private $paginationSchema;

    public function __construct(ResourceSchemaFactory $schemaFactory)
    {
        $this->schemaFactory = $schemaFactory;
    }

    public function new(string $path, string $resource): self
    {
        $self = new self($this->schemaFactory);
        $self->path = $path;
        $self->resourceClassName = $resource;

        return $self;
    }

    /**
     * @param mixed[] $singleIncludes
     */
    public function withSingleIncludes(array $singleIncludes): self
    {
        if (false === $this->getSingle) {
            throw ResourceApiEndpointsException::singleNotEnabled();
        }
        $this->singleIncludes = $singleIncludes;

        return $this;
    }

    /**
     * @param mixed[] $singleFields
     */
    public function withSingleFields(array $singleFields): self
    {
        if (false === $this->getSingle) {
            throw ResourceApiEndpointsException::singleNotEnabled();
        }
        $this->singleFields = $singleFields;

        return $this;
    }

    /**
     * @param mixed[] $collectionIncludes
     */
    public function withCollectionIncludes(array $collectionIncludes): self
    {
        if (false === $this->getCollection) {
            throw ResourceApiEndpointsException::collectionNotEnabled();
        }
        $this->collectionIncludes = $collectionIncludes;

        return $this;
    }

    /**
     * @param mixed[] $collectionFilters
     */
    public function withCollectionFilters(array $collectionFilters): self
    {
        if (false === $this->getCollection) {
            throw ResourceApiEndpointsException::collectionNotEnabled();
        }
        $this->collectionFilters = $collectionFilters;

        return $this;
    }

    /**
     * @param mixed[] $collectionSorts
     */
    public function withCollectionSortableAttributes(array $collectionSorts): self
    {
        if (false === $this->getCollection) {
            throw ResourceApiEndpointsException::collectionNotEnabled();
        }
        $this->collectionSorts = $collectionSorts;

        return $this;
    }

    public function withOffsetBasedPagination(): self
    {
        return $this->withCollectionPagination(new OffsetBasedPaginationQueryParam());
    }

    public function withPageBasedPagination(): self
    {
        return $this->withCollectionPagination(new PageBasedPaginationQueryParam());
    }

    public function withCollectionPagination(Schema $paginationSchema): self
    {
        if (false === $this->getCollection) {
            throw ResourceApiEndpointsException::collectionNotEnabled();
        }
        $this->paginationSchema = $paginationSchema;

        return $this;
    }

    public function withGetSingle(): self
    {
        $this->getSingle = true;

        return $this;
    }

    public function withGetCollection(): self
    {
        $this->getCollection = true;

        return $this;
    }

    public function withCreate(): self
    {
        $this->create = true;

        return $this;
    }

    public function withUpdate(): self
    {
        $this->update = true;

        return $this;
    }

    public function withDelete(): self
    {
        $this->delete = true;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function addToApi(Api $api): void
    {
        $readSchema = $this->schemaFactory->readSchema($this->resourceClassName);
        $createSchema = $this->schemaFactory->createSchema($this->resourceClassName);
        $updateSchema = $this->schemaFactory->updateSchema($this->resourceClassName);
        $relationshipsIdentifiers = $this->schemaFactory->relationshipsIdentifiers($this->resourceClassName);

        if (true === $this->getCollection) {
            /**
             * To generate proper `included` section of the response, we need to add read schemas for all resources for
             * which the inclusion is enabled. Therefore, we iterate over the passed array of `name` => `ApiModel` pairs
             * and generate the readSchema. Below you can see that all these schemas are added to the $api, and in the
             * CollectionResponse response proper `anyOf` schema is generated, referencing these schemas.
             */
            $collectionIncludedSchemas = array_map(
                [$this->schemaFactory, 'readSchema'],
                $this->collectionIncludes
            );

            $getCollectionEndpoint = new ResourceCollectionEndpoint(
                $readSchema,
                $this->path,
                $this->collectionFilters,
                $this->collectionSorts,
                $collectionIncludedSchemas,
                $this->collectionFields,
                $this->paginationSchema
            // @todo Add error responses (e.g. validation errors)
            );

            $api->addSchemas($relationshipsIdentifiers);
            $api->addEndpoint($getCollectionEndpoint);
            $api->addSchema($readSchema);
            $api->addSchemas($collectionIncludedSchemas);
        }

        if (true === $this->getSingle) {
            /**
             * To generate proper `included` section of the response, we need to add read schemas for all resources for
             * which the inclusion is enabled. Therefore, we iterate over the passed array of `name` => `ApiModel` pairs
             * and generate the readSchema. Below you can see that all these schemas are added to the $api, and in the
             * CollectionResponse response proper `anyOf` schema is generated, referencing these schemas.
             */
            $singleIncludedSchemas = array_map(
                [$this->schemaFactory, 'readSchema'],
                $this->singleIncludes
            );

            $getSingleResourceEndpoint = new GetResourceEndpoint(
                $this->schemaFactory->readSchema($this->resourceClassName),
                $this->path,
                $singleIncludedSchemas,
                $this->singleFields
            // @todo error responses
            );

            $api->addEndpoint($getSingleResourceEndpoint);
            $api->addSchema($readSchema);
            $api->addSchemas($singleIncludedSchemas);
        }

        if (true === $this->create) {
            $createResourceEndpoint = new CreateResourceEndpoint(
                $readSchema,
                $createSchema,
                $this->path
            );

            $api->addSchema($createSchema);
            $api->addEndpoint($createResourceEndpoint);
        }

        if (true === $this->update) {
            $createResourceEndpoint = new UpdateResourceEndpoint(
                $readSchema,
                $updateSchema,
                $this->path
            );

            $api->addSchema($updateSchema);
            $api->addEndpoint($createResourceEndpoint);
        }
    }
}
