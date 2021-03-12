<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Factory;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Definition\Encoding\PhpArrayToResourceEncoderInterface;
use Undabot\JsonApi\Definition\Exception\Request\RequestException;
use Undabot\JsonApi\Implementation\Encoding\Exception\JsonApiEncodingException;
use Undabot\JsonApi\Implementation\Factory\PaginationFactory;
use Undabot\JsonApi\Implementation\Model\Request\Filter\FilterSet;
use Undabot\JsonApi\Implementation\Model\Request\Sort\SortSet;
use Undabot\SymfonyJsonApi\Http\Model\Request\CreateResourceRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\UpdateResourceRequest;
use Undabot\SymfonyJsonApi\Http\Service\Validation\RequestValidator;

class RequestFactory
{
    private PhpArrayToResourceEncoderInterface$resourceEncoder;
    private RequestValidator$requestValidator;

    public function __construct(
        PhpArrayToResourceEncoderInterface $resourceEncoder,
        RequestValidator $requestValidator
    ) {
        $this->resourceEncoder = $resourceEncoder;
        $this->requestValidator = $requestValidator;
    }

    /**
     * @see https://jsonapi.org/format/#crud-creating
     *
     * @throws RequestException
     * @throws JsonApiEncodingException
     */
    public function createResourceRequest(
        Request $request,
        string $id = null
    ): CreateResourceRequest {
        $this->requestValidator->assertValidRequest($request);
        $requestPrimaryData = $this->getRequestPrimaryData($request);

        // If the server-side ID is passed as argument, we don't expect the Client to generate ID
        // https://jsonapi.org/format/#crud-creating-client-ids
        if (null !== $id) {
            $this->requestValidator->assertResourceIsWithoutClientGeneratedId($requestPrimaryData);
            $requestPrimaryData['id'] = $id;
        }

        $resource = $this->resourceEncoder->decode($requestPrimaryData);

        return new CreateResourceRequest($resource);
    }

    public function requestResourceHasClientSideGeneratedId(Request $request): bool
    {
        $requestPrimaryData = $this->getRequestPrimaryData($request);

        return \array_key_exists('id', $requestPrimaryData);
    }

    /**
     * @throws RequestException
     */
    public function getResourceRequest(Request $request, string $id): GetResourceRequest
    {
        $this->requestValidator->assertValidRequest($request);

        $includeString = $request->query->get(GetResourceRequest::INCLUDE_KEY, null);
        $include = null;
        if (null !== $includeString) {
            $include = explode(',', $includeString);
        }

        /** @var array<string,string> $fields */
        $fields = $request->query->get(GetResourceRequest::FIELDS_KEY, null);

        return new GetResourceRequest($id, $include, $fields);
    }

    /**
     * @throws RequestException
     */
    public function getResourceCollectionRequest(Request $request): GetResourceCollectionRequest
    {
        $this->requestValidator->assertValidRequest($request);

        $sortSet = $request->query->has(GetResourceCollectionRequest::SORT_KEY)
            ? SortSet::make($request->query->get(GetResourceCollectionRequest::SORT_KEY) ?? '')
            : null;

        /** @var array<string,int> $paginationFromRequest */
        $paginationFromRequest = $request->query->get(GetResourceCollectionRequest::PAGINATION_KEY) ?? [];
        $pagination = false === empty($paginationFromRequest)
            ? (new PaginationFactory())->fromArray($paginationFromRequest)
            : null;

        /** @var array<string,string> $filterFromRequest */
        $filterFromRequest = $request->query->get(GetResourceCollectionRequest::FILTER_KEY);
        $filterSet = $request->query->has(GetResourceCollectionRequest::FILTER_KEY)
            ? FilterSet::fromArray($filterFromRequest)
            : null;

        $include = $request->query->has(GetResourceCollectionRequest::INCLUDE_KEY)
            ? explode(',', $request->query->get(GetResourceCollectionRequest::INCLUDE_KEY) ?? '')
            : null;

        /** @var array<int,string> $fields */
        $fields = $request->query->get(GetResourceCollectionRequest::FIELDS_KEY, null);

        return new GetResourceCollectionRequest($pagination, $filterSet, $sortSet, $include, $fields);
    }

    /**
     * @throws RequestException
     * @throws JsonApiEncodingException
     * @throws AssertionFailedException
     */
    public function updateResourceRequest(Request $request, string $id): UpdateResourceRequest
    {
        $this->requestValidator->assertValidRequest($request);
        $requestPrimaryData = $this->getRequestPrimaryData($request);
        $this->requestValidator->assertValidUpdateRequestData($requestPrimaryData, $id);
        $resource = $this->resourceEncoder->decode($requestPrimaryData);

        return new UpdateResourceRequest($resource);
    }

    /**
     * @throws AssertionFailedException
     *
     * @return array<string, mixed>
     */
    private function getRequestPrimaryData(Request $request): array
    {
        /** @var string $rawRequestData */
        $rawRequestData = $request->getContent(false);
        Assertion::isJsonString($rawRequestData, 'Request data must be valid JSON');
        $requestData = json_decode($rawRequestData, true);

        Assertion::notNull($requestData, 'Request data must be parsable to a valid array');
        Assertion::isArray($requestData, 'Request data must be parsable to a valid array');
        Assertion::keyExists($requestData, 'data', 'The request MUST include a single resource object as primary data');

        return $requestData['data'];
    }
}
