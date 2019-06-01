<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Validation;

use Symfony\Component\HttpFoundation\Request;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestAcceptHeaderException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestContentTypeHeaderException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\JsonApiRequestException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedQueryStringParameterGivenException;

interface JsonApiRequestValidatorInterface
{
    /**
     * Validates HTTP request against general JSON:API specification rules:
     * - Accept header
     * - Content type header
     * - Support for given query params
     *
     * @throws JsonApiRequestException
     */
    public function makeSureRequestIsValidJsonApiRequest(Request $request): void;

    /**
     * Validates that HTTP request doesn't have Client-generated ID assigned to the resource
     *
     * @throws JsonApiRequestException
     */
    public function makeSureRequestResourceDoesntHaveClientGeneratedId(array $requestPrimaryData): void;

    /**
     * @throws JsonApiRequestException
     */
    public function makeSureRequestHasOnlyWhitelistedIncludeQueryParams(
        Request $request,
        array $whitelistedIncludeValues
    ): void;

    /**
     * @throws JsonApiRequestException
     */
    public function makeSureRequestHasOnlyWhitelistedFilterQueryParams(
        Request $request,
        array $filterableAttributes
    ): void;

    /**
     * @throws JsonApiRequestException
     */
    public function makeSureRequestHasOnlyWhitelistedSortQueryParams(
        Request $request,
        array $whitelistedSortValues
    ): void;

    /**
     * @throws JsonApiRequestException
     */
    public function makeSureRequestDoesntHaveSparseFieldsetQueryParams(Request $request): void;

    /**
     * @throws JsonApiRequestException
     */
    public function makeSureRequestDoesntHavePaginationQueryParams(Request $request);
}
