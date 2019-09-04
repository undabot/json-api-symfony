<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Validation;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Model\Request\Sort\Sort;
use Undabot\JsonApi\Model\Request\Sort\SortSet;
use Undabot\JsonApi\Exception\Request\ClientGeneratedIdIsNotAllowedException;
use Undabot\JsonApi\Exception\Request\InvalidRequestAcceptHeaderException;
use Undabot\JsonApi\Exception\Request\InvalidRequestContentTypeHeaderException;
use Undabot\JsonApi\Exception\Request\InvalidRequestDataException;
use Undabot\JsonApi\Exception\Request\UnsupportedFilterAttributeGivenException;
use Undabot\JsonApi\Exception\Request\UnsupportedIncludeValuesGivenException;
use Undabot\JsonApi\Exception\Request\UnsupportedMediaTypeException;
use Undabot\JsonApi\Exception\Request\UnsupportedPaginationRequestedException;
use Undabot\JsonApi\Exception\Request\UnsupportedQueryStringParameterGivenException;
use Undabot\JsonApi\Exception\Request\UnsupportedSortRequestedException;
use Undabot\JsonApi\Exception\Request\UnsupportedSparseFieldsetRequestedException;

class RequestValidator implements RequestValidatorInterface
{
    private $supportedQueryParamNames = [
        'include',
        'sort',
        'filter',
        'page',
        'fields',
    ];

    /**
     * Validates request according to JSON:API specification
     *
     * @param Request $request
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws UnsupportedMediaTypeException
     * @throws UnsupportedQueryStringParameterGivenException
     */
    public function assertValidRequest(Request $request): void
    {

        return;
        /*
         * Servers MUST respond with a 415 Unsupported Media Type status code if a request specifies the header
         * Content-Type: application/vnd.api+json with any media type parameters.
         */
        if ($request->headers->has('Content-Type') && is_string($request->headers->get('Content-Type'))) {
            $contentTypeHeader = explode(';', $request->headers->get('Content-Type'));

            if (count($contentTypeHeader) > 1) {
                $message = 'Media types are not allowed.';
                throw new UnsupportedMediaTypeException($message);
            }

            if ('application/vnd.api+json' !== $contentTypeHeader[0]) {
                $message = sprintf('Expected valid Json api content type, got %s', json_encode($contentTypeHeader));
                throw new InvalidRequestContentTypeHeaderException($message);
            }
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
                throw new Exception('Couldn\'t check accept headers');
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
    public function assertResourceIsWithoutClientGeneratedId(array $requestPrimaryData): void
    {
        if (true === array_key_exists('id', $requestPrimaryData)) {
            throw new ClientGeneratedIdIsNotAllowedException('Client is not permitted to set ID value.');
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
     * @throws AssertionFailedException
     */
    public function assertValidUpdateRequestData(array $data, string $id): void
    {
        if (false === Assertion::notEmptyKey($data, 'id')) {
            throw new InvalidRequestDataException('Update request must have resource ID');
        }

        Assertion::same($data['id'], $id, 'Resource with invalid ID given');
    }

}
