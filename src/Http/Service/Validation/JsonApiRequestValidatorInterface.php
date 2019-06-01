<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Request\Validation;

use Symfony\Component\HttpFoundation\Request;
use Undabot\SymfonyJsonApi\Http\Request\Exception\ClientGeneratedIdIsNotAllowedException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\InvalidRequestAcceptHeaderException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\InvalidRequestContentTypeHeaderException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedFilterAttributeGivenException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedIncludeValuesGivenException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedPaginationRequestedException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedQueryStringParameterGivenException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedSortRequestedException;
use Undabot\SymfonyJsonApi\Http\Request\Exception\UnsupportedSparseFieldsetRequestedException;

interface JsonApiRequestValidatorInterface
{
    /**
     * Validates HTTP request against general JSON:API specification rules:
     * - Accept header
     * - Content type header
     * - Support for given query params
     *
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws UnsupportedQueryStringParameterGivenException
     */
    public function makeSureRequestIsValidJsonApiRequest(Request $request): void;

    /**
     * Validates that HTTP request doesn't have Client-generated ID assigned to the resource
     *
     * @throws ClientGeneratedIdIsNotAllowedException
     */
    public function makeSureRequestResourceDoesntHaveClientGeneratedId(array $requestPrimaryData): void;

    /**
     * @throws UnsupportedIncludeValuesGivenException
     */
    public function makeSureRequestHasOnlyWhitelistedIncludeQueryParams(
        Request $request,
        array $whitelistedIncludeValues
    ): void;

    /**
     * @throws UnsupportedSortRequestedException
     */
    public function makeSureRequestHasOnlyWhitelistedFilterQueryParams(
        Request $request,
        array $filterableAttributes
    ): void;

    /**
     * @throws UnsupportedFilterAttributeGivenException
     */
    public function makeSureRequestHasOnlyWhitelistedSortQueryParams(
        Request $request,
        array $whitelistedSortValues
    ): void;

    /**
     * @throws UnsupportedSparseFieldsetRequestedException
     */
    public function makeSureRequestDoesntHaveSparseFieldsetQueryParams(Request $request): void;

    /**
     * @throws UnsupportedPaginationRequestedException
     */
    public function makeSureRequestDoesntHavePaginationQueryParams(Request $request);
}
