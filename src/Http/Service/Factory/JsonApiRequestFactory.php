<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Factory;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Encoding\Exception\PhpArrayEncodingException;
use Undabot\JsonApi\Encoding\PhpArrayToResourceEncoderInterface;
use Undabot\JsonApi\Model\Request\CreateResourceRequestInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Util\Assert\Assert;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestDataException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\JsonApiRequestException;
use Undabot\SymfonyJsonApi\Http\Model\Request\CreateResourceRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\UpdateResourceRequest;
use Undabot\SymfonyJsonApi\Http\Service\Validation\RequestValidator;

class JsonApiRequestFactory
{
    /** @var PhpArrayToResourceEncoderInterface */
    private $phpArrayToResourceEncoder;

    /** @var RequestValidator */
    private $jsonApiRequestValidator;

    public function __construct(
        PhpArrayToResourceEncoderInterface $phpArrayToResourceEncoder,
        RequestValidator $jsonApiRequestValidator
    ) {
        $this->phpArrayToResourceEncoder = $phpArrayToResourceEncoder;
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
     * @throws PhpArrayEncodingException
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

        $resource = $this->phpArrayToResourceEncoder->decode($requestPrimaryData);

        return $resource;
    }

    /**
     * @throws PhpArrayEncodingException
     */
    private function getSingleResourceObjectFromRequest(array $requestPrimaryData): ResourceInterface
    {
        $resource = $this->phpArrayToResourceEncoder->decode($requestPrimaryData);

        return $resource;
    }

    /**
     * @see https://jsonapi.org/format/#crud-creating
     *
     * @throws JsonApiRequestException
     * @throws PhpArrayEncodingException
     */
    public function makeCreateResourceRequestWithServerGeneratedId(
        Request $request,
        string $id
    ): CreateResourceRequestInterface {
        return $this->makeCreateResourceRequest($request, false, $id);
    }

    /**
     * @see https://jsonapi.org/format/#crud-creating
     *
     * @throws JsonApiRequestException
     * @throws PhpArrayEncodingException
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
     * @throws JsonApiRequestException
     */
    public function singleResourceRequest(
        Request $request,
        string $id,
        array $whitelistedIncludeValues = []
    ): GetResourceRequest {
        $this->jsonApiRequestValidator->makeSureRequestIsValidJsonApiRequest($request);

        $this->jsonApiRequestValidator->makeSureRequestHasOnlyWhitelistedIncludeQueryParams(
            $request,
            $whitelistedIncludeValues
        );

        $includeString = $request->query->get('include', null);

        $include = null;
        if (null !== $includeString) {
            $include = explode(',', $includeString);
        }

        $fields = $request->query->get('fields', null);

        return new GetResourceRequest(
            $id,
            $include,
            $fields
        );
    }

    /**
     * @throws JsonApiRequestException
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
     * @throws JsonApiRequestException
     * @throws PhpArrayEncodingException
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
