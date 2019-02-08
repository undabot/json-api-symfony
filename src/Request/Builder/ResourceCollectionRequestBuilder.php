<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Request\Builder;

use Symfony\Component\HttpFoundation\Request;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestAcceptHeaderException;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestContentTypeHeaderException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedFilterAttributeGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedIncludeValuesGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedPaginationRequestedException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedQueryStringParameterGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedSortRequestedException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedSparseFieldsetRequestedException;
use Undabot\SymfonyJsonApi\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Request\Validation\JsonApiRequestValidatorInterface;

class ResourceCollectionRequestBuilder
{
    /** @var Request */
    private $request;

    /** @var JsonApiRequestValidatorInterface */
    private $requestValidator;

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

    public function __construct(JsonApiRequestValidatorInterface $requestValidator)
    {
        $this->requestValidator = $requestValidator;
    }

    /**
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws UnsupportedQueryStringParameterGivenException
     */
    public function setRequest(Request $request): self
    {
        $this->requestValidator->makeSureRequestIsValidJsonApiRequest($request);
        $this->request = $request;

        return $this;
    }

    public function withAllowedFilters(array $allowedFilters)
    {
        $this->allowedFilters = $allowedFilters;

        return $this;
    }

    public function withAllowedIncludedResources(array $allowedIncluded)
    {
        $this->allowedIncluded = $allowedIncluded;

        return $this;
    }

    public function withAllowedSortableFields(array $allowedSortables)
    {
        $this->allowedSortables = $allowedSortables;

        return $this;
    }

    public function allowFieldSelection(bool $sparseFieldsAllowed)
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
     * @throws UnsupportedFilterAttributeGivenException
     * @throws UnsupportedIncludeValuesGivenException
     * @throws UnsupportedPaginationRequestedException
     * @throws UnsupportedSortRequestedException
     * @throws UnsupportedSparseFieldsetRequestedException
     */
    public function build(): GetResourceCollectionRequest
    {
        $this->requestValidator->makeSureRequestHasOnlyWhitelistedFilterQueryParams($this->request, $this->allowedFilters);
        $this->requestValidator->makeSureRequestHasOnlyWhitelistedIncludeQueryParams($this->request,
            $this->allowedIncluded);
        $this->requestValidator->makeSureRequestHasOnlyWhitelistedSortQueryParams($this->request,
            $this->allowedSortables);

        if (false === $this->sparseFieldsAllowed) {
            $this->requestValidator->makeSureRequestDoesntHaveSparseFieldsetQueryParams($this->request);
        }

        if (false === $this->paginationAllowed) {
            $this->requestValidator->makeSureRequestDoesntHavePaginationQueryParams($this->request);
        }

        return GetResourceCollectionRequest::createFromRequest($this->request);
    }
}
