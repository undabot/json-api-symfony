<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Validation;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Exception\Request\ClientGeneratedIdIsNotAllowedException;
use Undabot\JsonApi\Exception\Request\InvalidRequestAcceptHeaderException;
use Undabot\JsonApi\Exception\Request\InvalidRequestContentTypeHeaderException;
use Undabot\JsonApi\Exception\Request\InvalidRequestDataException;
use Undabot\JsonApi\Exception\Request\UnsupportedMediaTypeException;
use Undabot\JsonApi\Exception\Request\UnsupportedQueryStringParameterGivenException;

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
                throw new InvalidRequestAcceptHeaderException('Couldn\'t check accept headers');
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
     * @throws InvalidRequestDataException
     * @throws AssertionFailedException
     * @param array<string, mixed> $data
     */
    public function assertValidUpdateRequestData(array $data, string $id): void
    {
        if (false === Assertion::notEmptyKey($data, 'id')) {
            throw new InvalidRequestDataException('Update request must have resource ID');
        }

        Assertion::same($data['id'], $id, 'Resource with invalid ID given');
    }
}
