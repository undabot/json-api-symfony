<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Validation;

use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Exception\Request\RequestException;

interface RequestValidatorInterface
{
    /**
     * Validates HTTP request against general JSON:API specification rules:
     * - Accept header
     * - Content type header
     * - Support for given query params
     *
     * @throws RequestException
     */
    public function assertValidRequest(Request $request): void;

    /**
     * Validates that HTTP request doesn't have Client-generated ID assigned to the resource
     *
     * @throws RequestException
     */
    public function assertResourceIsWithoutClientGeneratedId(array $requestPrimaryData): void;

    /**
     * @throws RequestException
     */
    public function makeSureRequestHasOnlyWhitelistedIncludeQueryParams(
        Request $request,
        array $whitelistedIncludeValues
    ): void;

    /**
     * @throws RequestException
     */
    public function makeSureRequestHasOnlyWhitelistedFilterQueryParams(
        Request $request,
        array $filterableAttributes
    ): void;

    /**
     * @throws RequestException
     */
    public function makeSureRequestHasOnlyWhitelistedSortQueryParams(
        Request $request,
        array $whitelistedSortValues
    ): void;

    /**
     * @throws RequestException
     */
    public function makeSureRequestDoesntHaveSparseFieldsetQueryParams(Request $request): void;

    /**
     * @throws RequestException
     */
    public function makeSureRequestDoesntHavePaginationQueryParams(Request $request);
}
