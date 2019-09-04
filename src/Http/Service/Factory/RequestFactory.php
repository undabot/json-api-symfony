<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Factory;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Encoding\Exception\PhpArrayEncodingException;
use Undabot\JsonApi\Encoding\PhpArrayToResourceEncoderInterface;
use Undabot\JsonApi\Exception\Request\RequestException;
use Undabot\JsonApi\Model\Request\Filter\FilterSet;
use Undabot\JsonApi\Model\Request\Sort\SortSet;
use Undabot\JsonApi\Util\Assert\Assert;
use Undabot\JsonApi\Exception\Request\InvalidRequestDataException;
use Undabot\SymfonyJsonApi\Http\Model\Request\CreateResourceRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\UpdateResourceRequest;
use Undabot\SymfonyJsonApi\Http\Service\Validation\RequestValidator;

class RequestFactory
{
    /** @var PhpArrayToResourceEncoderInterface */
    private $resourceEncoder;

    /** @var RequestValidator */
    private $requestValidator;

    public function __construct(
        PhpArrayToResourceEncoderInterface $resourceEncoder,
        RequestValidator $requestValidator
    ) {
        $this->resourceEncoder = $resourceEncoder;
        $this->requestValidator = $requestValidator;
    }

    /**
     * @throws InvalidRequestDataException
     * @throws AssertionFailedException
     */
    private function getRequestPrimaryData(Request $request): array
    {
        $rawRequestData = $request->getContent(false);
        Assertion::isJsonString($rawRequestData, 'Request data must be valid JSON');
        $requestData = json_decode($rawRequestData, true);

        if (null === $requestData) {
            throw new InvalidRequestDataException('Request data must be valid JSON');
        }

        if (false === Assert::arrayHasRequiredKeys($requestData, ['data'])) {
            throw new InvalidRequestDataException('The request MUST include a single resource object as primary data.');
        }

        return $requestData['data'];
    }

    /**
     * @see https://jsonapi.org/format/#crud-creating
     *
     * @throws RequestException
     * @throws PhpArrayEncodingException
     * @throws AssertionFailedException
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
            ? SortSet::make($request->query->get(GetResourceCollectionRequest::SORT_KEY))
            : null;

        $pagination = $request->query->has(GetResourceCollectionRequest::PAGINATION_KEY)
            ? (new PaginationFactory())->fromArray($request->query->get(GetResourceCollectionRequest::PAGINATION_KEY))
            : null;

        $filterSet = $request->query->has(GetResourceCollectionRequest::FILTER_KEY)
            ? FilterSet::fromArray($request->query->get(GetResourceCollectionRequest::FILTER_KEY))
            : null;

        $include = $request->query->has(GetResourceCollectionRequest::INCLUDE_KEY)
            ? explode(',', $request->query->get(GetResourceCollectionRequest::INCLUDE_KEY))
            : null;

        $fields = $request->query->get(GetResourceCollectionRequest::FIELDS_KEY, null);

        return new GetResourceCollectionRequest($pagination, $filterSet, $sortSet, $include, $fields);
    }

    /**
     * @throws RequestException
     * @throws PhpArrayEncodingException
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
}
