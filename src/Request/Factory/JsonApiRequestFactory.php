<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Request\Factory;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Encoding\PhpArray\Decode\ResourceJsonDecoderInterface;
use Undabot\JsonApi\Encoding\PhpArray\Exception\PhpArrayDecodingException;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Util\Assert\Assert;
use Undabot\SymfonyJsonApi\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Request\CreateResourceRequest;
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
use Undabot\SymfonyJsonApi\Request\GetSingleResourceRequest;
use Undabot\SymfonyJsonApi\Request\UpdateResourceRequest;
use Undabot\SymfonyJsonApi\Request\Validation\JsonApiRequestValidator;

class JsonApiRequestFactory
{
    /** @var ResourceJsonDecoderInterface */
    private $resourceDecoder;

    /** @var JsonApiRequestValidator */
    private $jsonApiRequestValidator;

    public function __construct(
        ResourceJsonDecoderInterface $resourceDecoder,
        JsonApiRequestValidator $jsonApiRequestValidator
    ) {
        $this->resourceDecoder = $resourceDecoder;
        $this->jsonApiRequestValidator = $jsonApiRequestValidator;
    }

    /**
     * @throws InvalidRequestDataException
     */
    private function getRequestData(Request $request): array
    {
        /** @var string $rawData */
        $rawData = $request->getContent(false);
        $data = json_decode($rawData, true);

        if (null === $data) {
            throw new InvalidRequestDataException('Request data is not valid JSON');
        }

        return $data;
    }

    /**
     * @throws InvalidRequestDataException
     */
    private function getRequestPrimaryData(Request $request): array
    {
        $rawRequestData = $this->getRequestData($request);
        if (false === Assert::arrayHasRequiredKeys($rawRequestData, ['data'])) {
            throw new InvalidRequestDataException('The request MUST include a single resource object as primary data.');
        }

        return $rawRequestData['data'];
    }

    /**
     * @throws PhpArrayDecodingException
     * @throws InvalidRequestDataException
     */
    private function getSingleResourceObjectFromRequestWithAssignedId(
        array $requestPrimaryData,
        string $id
    ): ResourceInterface {
        if (true === array_key_exists('id', $requestPrimaryData)) {
            throw new InvalidRequestDataException('Request primary data already contains `id` member, can\'t assign a new one');
        }

        $requestPrimaryData['id'] = $id;
        $resource = $this->resourceDecoder->decode($requestPrimaryData);

        return $resource;
    }

    /**
     * @throws PhpArrayDecodingException
     */
    private function getSingleResourceObjectFromRequest(array $requestPrimaryData): ResourceInterface
    {
        $resource = $this->resourceDecoder->decode($requestPrimaryData);

        return $resource;
    }

    /**
     * @see https://jsonapi.org/format/#crud-creating
     *
     * @throws ClientGeneratedIdIsNotAllowedException
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws InvalidRequestDataException
     * @throws PhpArrayDecodingException
     */
    public function makeCreateResourceRequestWithServerGeneratedId(Request $request, string $id)
    {
        return $this->makeCreateResourceRequest($request, false, $id);
    }

    /**
     * @see https://jsonapi.org/format/#crud-creating
     *
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws InvalidRequestDataException
     * @throws ClientGeneratedIdIsNotAllowedException
     * @throws PhpArrayDecodingException
     * @throws UnsupportedQueryStringParameterGivenException
     */
    public function makeCreateResourceRequest(
        Request $request,
        bool $allowClientGeneratedIds,
        string $id = null
    ): CreateResourceRequest {
        if (false === $allowClientGeneratedIds && null === $id) {
            throw new InvalidArgumentException('If the client-generated IDs aren\'t allowed, `$id` parameter is required');
        }

        $this->jsonApiRequestValidator->makeSureRequestIsValidJsonApiRequest($request);
        $requestPrimaryData = $this->getRequestPrimaryData($request);

        if (false === $allowClientGeneratedIds) {
            $this->jsonApiRequestValidator->makeSureRequestResourceDoesntHaveClientGeneratedId($requestPrimaryData);
        }

        if (null === $id) {
            $resource = $this->getSingleResourceObjectFromRequest($requestPrimaryData);

            return new CreateResourceRequest($resource);
        }

        $resource = $this->getSingleResourceObjectFromRequestWithAssignedId($requestPrimaryData, $id);

        return new CreateResourceRequest($resource);
    }

    /**
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws UnsupportedQueryStringParameterGivenException
     * @throws UnsupportedIncludeValuesGivenException
     */
    public function makeGetSingleResuorceRequest(
        Request $request,
        string $id,
        array $whitelistedIncludeValues = []
    ): GetSingleResourceRequest {
        $this->jsonApiRequestValidator->makeSureRequestIsValidJsonApiRequest($request);

        $this->jsonApiRequestValidator->makeSureRequestHasOnlyWhitelistedIncludeQueryParams(
            $request,
            $whitelistedIncludeValues
        );

        return new GetSingleResourceRequest(
            $id,
            $request->query->get('include'),
            $request->query->get('fields')
        );
    }

    /**
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws UnsupportedQueryStringParameterGivenException
     * @throws UnsupportedIncludeValuesGivenException
     * @throws UnsupportedFilterAttributeGivenException
     * @throws UnsupportedSortRequestedException
     * @throws UnsupportedSparseFieldsetRequestedException
     * @throws UnsupportedPaginationRequestedException
     */
    public function makeGetResourceCollectionRequest(
        Request $request,
        array $includableRelationships = [],
        array $sortableAttributes = [],
        array $filterableAttributes = [],
        bool $enablePagination = true,
        bool $enableFieldsSelection = false
    ): GetResourceCollectionRequest {
        $this->jsonApiRequestValidator->makeSureRequestIsValidJsonApiRequest($request);
        $this->jsonApiRequestValidator->makeSureRequestHasOnlyWhitelistedIncludeQueryParams(
            $request,
            $includableRelationships
        );
        $this->jsonApiRequestValidator->makeSureRequestHasOnlyWhitelistedSortQueryParams(
            $request,
            $sortableAttributes
        );
        $this->jsonApiRequestValidator->makeSureRequestHasOnlyWhitelistedFilterQueryParams(
            $request,
            $filterableAttributes
        );

        if (false === $enableFieldsSelection) {
            $this->jsonApiRequestValidator->makeSureRequestDoesntHaveSparseFieldsetQueryParams($request);
        }

        if (false === $enablePagination) {
            $this->jsonApiRequestValidator->makeSureRequestDoesntHavePaginationQueryParams($request);
        }

        return GetResourceCollectionRequest::createFromRequest($request);
    }

    /**
     * @throws InvalidRequestAcceptHeaderException
     * @throws InvalidRequestContentTypeHeaderException
     * @throws InvalidRequestDataException
     * @throws PhpArrayDecodingException
     * @throws UnsupportedQueryStringParameterGivenException
     */
    public function makeUpdateResourceRequest(Request $request, string $id): UpdateResourceRequest
    {
        $this->jsonApiRequestValidator->makeSureRequestIsValidJsonApiRequest($request);
        $requestPrimaryData = $this->getRequestPrimaryData($request);
        $resource = $this->getSingleResourceObjectFromRequest($requestPrimaryData);
        $this->jsonApiRequestValidator->makeSureResourceHasTheSameId($id, $resource);

        return new UpdateResourceRequest($resource);
    }
}
