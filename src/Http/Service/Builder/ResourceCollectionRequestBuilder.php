<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Request\Builder;

use Symfony\Component\HttpFoundation\Request;
use Undabot\SymfonyJsonApi\Http\Request\Exception\InvalidRequestAcceptHeaderException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\InvalidRequestContentTypeHeaderException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedFilterAttributeGivenException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedIncludeValuesGivenException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedPaginationRequestedException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedQueryStringParameterGivenException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedSortRequestedException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedSparseFieldsetRequestedException;
use Undabot\SymfonyJsonApi\Http\Request\Factory\JsonApiRequestFactory;
use Undabot\SymfonyJsonApi\Http\Request\GetResourceCollectionRequest;

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
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws UnsupportedFilterAttributeGivenException
     * @throws UnsupportedIncludeValuesGivenException
     * @throws UnsupportedPaginationRequestedException
     * @throws UnsupportedQueryStringParameterGivenException
     * @throws UnsupportedSortRequestedException
     * @throws UnsupportedSparseFieldsetRequestedException
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
