<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Builder;

use Symfony\Component\HttpFoundation\Request;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestAcceptHeaderException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestContentTypeHeaderException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\JsonApiRequestException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedFilterAttributeGivenException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedIncludeValuesGivenException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedPaginationRequestedException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedQueryStringParameterGivenException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedSortRequestedException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedSparseFieldsetRequestedException;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Service\Factory\JsonApiRequestFactory;

class ResourceCollectionRequestBuilder
{
    /** @var JsonApiRequestFactory */
    private $requestFactory;

    /** @var Request */
    private $request;

    /** @var array */
    private $allowedFilters = [];

    /** @var array */
    private $allowedIncluded = [];

    /** @var array */
    private $allowedSortables = [];

    /** @var bool */
    private $sparseFieldsAllowed = false;

    /** @var bool */
    private $paginationAllowed = true;

    public function __construct(JsonApiRequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /** @return ResourceCollectionRequestBuilder */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function withAllowedFilters(array $allowedFilters): self
    {
        $this->allowedFilters = $allowedFilters;

        return $this;
    }

    public function withAllowedIncludedResources(array $allowedIncluded): self
    {
        $this->allowedIncluded = $allowedIncluded;

        return $this;
    }

    public function withAllowedSortableFields(array $allowedSortables): self
    {
        $this->allowedSortables = $allowedSortables;

        return $this;
    }

    public function allowFieldSelection(bool $sparseFieldsAllowed): self
    {
        $this->sparseFieldsAllowed = $sparseFieldsAllowed;

        return $this;
    }

    public function allowPagination(bool $allowPagination)
    {
        $this->paginationAllowed = $allowPagination;

        return $this;
    }

    /**
     * @throws JsonApiRequestException
     */
    public function build(): GetResourceCollectionRequest
    {
        return $this->requestFactory->makeGetResourceCollectionRequest(
            $this->request,
            $this->allowedIncluded,
            $this->allowedSortables,
            $this->allowedFilters,
            $this->paginationAllowed,
            $this->sparseFieldsAllowed
        );
    }
}
