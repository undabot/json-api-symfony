<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Validation;

use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Definition\Exception\Request\RequestException;

interface RequestValidatorInterface
{
    /**
     * Validates HTTP request against general JSON:API specification rules:
     * - Accept header
     * - Content type header
     * - Support for given query params.
     *
     * @throws RequestException
     */
    public function assertValidRequest(Request $request): void;

    /**
     * Validates that HTTP request doesn't have Client-generated ID assigned to the resource.
     *
     * @param array<string, mixed> $requestPrimaryData
     */
    public function assertResourceIsWithoutClientGeneratedId(array $requestPrimaryData): void;
}
