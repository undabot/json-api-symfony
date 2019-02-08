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
use Undabot\SymfonyJsonApi\Request\Factory\JsonApiRequestFactory;
use Undabot\SymfonyJsonApi\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Request\Validation\JsonApiRequestValidatorInterface;

class ResourceCollectionRequestBuilder
{
    /** @var JsonApiRequestValidatorInterface */
    private $requestValidator;

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

    public function __construct(JsonApiRequestFactory $requestFactory, JsonApiRequestValidatorInterface $requestValidator)
    {
        $this->requestFactory = $requestFactory;
        $this->requestValidator = $requestValidator;
    }

    /**
     * @return ResourceCollectionRequestBuilder
     *
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
