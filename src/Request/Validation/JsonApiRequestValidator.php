<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Request\Validation;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Model\Request\Sort\Sort;
use Undabot\JsonApi\Model\Request\Sort\SortSet;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Request\Exception\ClientGeneratedIdIsNotAllowedException;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestAcceptHeaderException;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestContentTypeHeaderException;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestDataException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedFilterAttributeGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedIncludeValuesGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedPaginationRequestedException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedQueryStringParameterGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedSortRequestedException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedSparseFieldsetRequestedException;

class JsonApiRequestValidator implements JsonApiRequestValidatorInterface
{
    private $supportedQueryParamNames = [
        'include',
        'sort',
        'filter',
        'page',
        'fields',
    ];

    public function makeSureRequestIsValidJsonApiRequest(Request $request): void
    {
        return;

        /*
         * Servers MUST respond with a 415 Unsupported Media Type status code if a request specifies the header
         * Content-Type: application/vnd.api+json with any media type parameters.
         */
        if ('application/vnd.api+json' !== $request->headers->get('Content-Type')) {
            throw new InvalidRequestContentTypeHeaderException();
        }

        /*
         * Servers MUST respond with a 406 Not Acceptable status code if a request’s Accept header contains the
         * JSON:API media type and all instances of that media type are modified with media type parameters.
         */
        if (true === $request->headers->has('Accept')) {
            $accepts = $request->headers->get('Accept');

            if (true === is_string($accepts)) {
                $accepts = explode(',', $accepts);
            }

            if (false === is_array($accepts)) {
                throw new Exception('Coudldnt check headers');
            }

            if (false === in_array('application/vnd.api+json', $accepts)) {
                throw new InvalidRequestAcceptHeaderException();
            }
        }

        /*
         * If a server encounters a query parameter that does not follow the naming conventions above, and the server
         * does not know how to process it as a query parameter from this specification, it MUST return 400 Bad Request.
         *
         * @see https://jsonapi.org/format/#query-parameters
         */
        $queryParams = $request->query->all();
        $queryParamNames = array_keys($queryParams);
        $unsupportedQueryParams = array_diff($queryParamNames, $this->supportedQueryParamNames);

        if (0 !== count($unsupportedQueryParams)) {
            $message = sprintf('Unsupported query params given: %s', implode(', ', $unsupportedQueryParams));
            throw new UnsupportedQueryStringParameterGivenException($message);
        }
    }

    /**
     * @throws ClientGeneratedIdIsNotAllowedException
     */
    public function makeSureRequestResourceDoesntHaveClientGeneratedId(array $requestPrimaryData): void
    {
        if (true === array_key_exists('id', $requestPrimaryData)) {
            throw new ClientGeneratedIdIsNotAllowedException();
        }
    }

    /**
     * @throws UnsupportedIncludeValuesGivenException
     */
    public function makeSureRequestHasOnlyWhitelistedIncludeQueryParams(
        Request $request,
        array $whitelistedIncludeValues
    ): void {
        $requestedIncludeString = $request->query->get('include', null);
        if (null === $requestedIncludeString) {
            return;
        }

        $includes = explode(',', $requestedIncludeString);
        $unsupportedIncludeValues = array_diff($includes, $whitelistedIncludeValues);

        if (0 !== count($unsupportedIncludeValues)) {
            $message = sprintf('Unsupported include query params given: %s', implode(', ', $unsupportedIncludeValues));
            throw new UnsupportedIncludeValuesGivenException($message);
        }
    }

    /**
     * @throws UnsupportedSortRequestedException
     */
    public function makeSureRequestHasOnlyWhitelistedSortQueryParams(
        Request $request,
        array $whitelistedSortValues
    ): void {
        $requestedSortString = $request->query->get('sort', null);
        if (null === $requestedSortString) {
            return;
        }

        $sortSet = SortSet::make($requestedSortString);
        $unsupportedSorts = [];

        /** @var Sort $sort */
        foreach ($sortSet as $sort) {
            if (false === in_array($sort->getAttribute(), $whitelistedSortValues)) {
                $unsupportedSorts[] = $sort->getAttribute();
            }
        }

        if (0 !== count($unsupportedSorts)) {
            $message = sprintf('Unsupported sort query params given: %s', implode(', ', $unsupportedSorts));
            throw new UnsupportedSortRequestedException($message);
        }
    }

    /**
     * @throws UnsupportedFilterAttributeGivenException
     */
    public function makeSureRequestHasOnlyWhitelistedFilterQueryParams(
        Request $request,
        array $filterableAttributes
    ): void {
        $requestedFilters = $request->query->get('filter', null);
        if (null === $requestedFilters) {
            return;
        }

        $requestedFilterAttributes = array_keys($requestedFilters);
        $unsupportedFilterAttributes = array_diff($requestedFilterAttributes, $filterableAttributes);

        if (0 !== count($unsupportedFilterAttributes)) {
            $message = sprintf('Unsupported filter attributes given: %s', implode(', ', $unsupportedFilterAttributes));
            throw new UnsupportedFilterAttributeGivenException($message);
        }
    }

    /**
     * @throws UnsupportedSparseFieldsetRequestedException
     */
    public function makeSureRequestDoesntHaveSparseFieldsetQueryParams(Request $request): void
    {
        $requestedFields = $request->query->get('fields', null);
        if (null === $requestedFields) {
            return;
        }

        throw new UnsupportedSparseFieldsetRequestedException();
    }

    /**
     * @throws UnsupportedPaginationRequestedException
     */
    public function makeSureRequestDoesntHavePaginationQueryParams(Request $request): void
    {
        $requestedPagination = $request->query->get('page', null);
        if (null === $requestedPagination) {
            return;
        }

        throw new UnsupportedPaginationRequestedException();
    }

    /**
     * @throws InvalidRequestDataException
     */
    public function makeSureResourceHasTheSameId(string $id, ResourceInterface $resource): void
    {
        if ($resource->getId() !== $id) {
            throw new InvalidRequestDataException('Resource with invalid ID given');
        }
    }
}
