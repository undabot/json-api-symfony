<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Factory;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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

final class RequestFactory
{
    private array $requestData;

    public function __construct(
        private PhpArrayToResourceEncoderInterface $resourceEncoder,
        private RequestValidator $requestValidator,
        private RequestStack $requestStack,
    ) {}

    /**
     * @see https://jsonapi.org/format/#crud-creating
     *
     * @throws RequestException
     * @throws JsonApiEncodingException
     */
    public function createResourceRequest(): CreateResourceRequest
    {
        $request = $this->requestStack->getMainRequest();
        Assertion::isInstanceOf($request, Request::class);
        $id = $request->attributes->get('id');
        $this->requestValidator->assertValidRequest($request);
        $requestPrimaryData = $this->getRequestPrimaryData();

        /** If the server-side ID is passed as argument, we don't expect the Client to generate ID.
         * @see https://jsonapi.org/format/#crud-creating-client-ids
         */
        if (null !== $id) {
            $this->requestValidator->assertResourceIsWithoutClientGeneratedId($requestPrimaryData);
            $requestPrimaryData['id'] = $id;
        }

        /**
         * If we have lid sent as id we will pass it as resource id.
         *
         * @see https://jsonapi.org/format/#document-resource-object-identification
         */
        $lid = $this->getResourceLid();
        if (null !== $lid) {
            $requestPrimaryData['id'] = $lid;
            unset($requestPrimaryData['lid']);
        }

        $resource = $this->resourceEncoder->decode($requestPrimaryData);

        return new CreateResourceRequest($resource);
    }

    public function requestResourceHasClientSideGeneratedId(): bool
    {
        $requestPrimaryData = $this->getRequestPrimaryData();

        return \array_key_exists('id', $requestPrimaryData);
    }

    /**
     * @throws RequestException
     */
    public function getResourceRequest(): GetResourceRequest
    {
        $request = $this->requestStack->getMainRequest();
        Assertion::isInstanceOf($request, Request::class);
        $id = $request->attributes->get('id');
        $this->requestValidator->assertValidRequest($request);

        $includeString = $request->query->all()[GetResourceRequest::INCLUDE_KEY] ?? null;
        $include = null;
        if (null !== $includeString) {
            $include = explode(',', $includeString);
        }

        /** @var null|array<string,string> $fields */
        $fields = $request->query->all()[GetResourceRequest::FIELDS_KEY] ?? null;

        return new GetResourceRequest($id, $include, $fields);
    }

    /**
     * @throws RequestException
     */
    public function getResourceCollectionRequest(): GetResourceCollectionRequest
    {
        $request = $this->requestStack->getMainRequest();
        Assertion::isInstanceOf($request, Request::class);
        $this->requestValidator->assertValidRequest($request);

        $sortFromRequest = $request->query->all()[GetResourceCollectionRequest::SORT_KEY] ?? '';
        $sortSet = (true === \is_string($sortFromRequest) && false === empty($sortFromRequest)) ? SortSet::make($sortFromRequest) : null;

        /** @var array<string,int> $paginationFromRequest */
        $paginationFromRequest = $request->query->all()[GetResourceCollectionRequest::PAGINATION_KEY] ?? [];
        $pagination = false === empty($paginationFromRequest)
            ? (new PaginationFactory())->fromArray($paginationFromRequest)
            : null;

        /** @var null|array<string,string> $filterFromRequest */
        $filterFromRequest = $request->query->all()[GetResourceCollectionRequest::FILTER_KEY] ?? null;
        $filterSet = null !== $filterFromRequest ? FilterSet::fromArray($filterFromRequest) : null;

        $includeFromRequest = $request->query->all()[GetResourceCollectionRequest::INCLUDE_KEY] ?? '';
        $include = (true === \is_string($includeFromRequest) && false === empty($includeFromRequest))
            ? explode(',', $includeFromRequest)
            : null;

        /** @var null|array<int,string> $fields */
        $fields = $request->query->all()[GetResourceCollectionRequest::FIELDS_KEY] ?? null;

        return new GetResourceCollectionRequest($pagination, $filterSet, $sortSet, $include, $fields);
    }

    /**
     * @throws RequestException
     * @throws JsonApiEncodingException
     * @throws AssertionFailedException
     */
    public function updateResourceRequest(): UpdateResourceRequest
    {
        $request = $this->requestStack->getMainRequest();
        Assertion::isInstanceOf($request, Request::class);
        $id = $request->attributes->get('id');
        $this->requestValidator->assertValidRequest($request);
        $requestPrimaryData = $this->getRequestPrimaryData();
        $this->requestValidator->assertValidUpdateRequestData($requestPrimaryData, $id);
        $resource = $this->resourceEncoder->decode($requestPrimaryData);

        return new UpdateResourceRequest($resource);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws AssertionFailedException
     */
    private function getRequestPrimaryData(): array
    {
        $request = $this->requestStack->getMainRequest();
        Assertion::isInstanceOf($request, Request::class);
        if (false === empty($this->requestData)) {
            return $this->requestData;
        }

        /** @var string $rawRequestData */
        $rawRequestData = $request->getContent();
        Assertion::isJsonString($rawRequestData, 'Request data must be valid JSON');
        $requestData = json_decode($rawRequestData, true);

        Assertion::notNull($requestData, 'Request data must be parsable to a valid array');
        Assertion::isArray($requestData, 'Request data must be parsable to a valid array');
        Assertion::keyExists($requestData, 'data', 'The request MUST include a single resource object as primary data');
        $this->requestData = $requestData['data'];

        return $requestData['data'];
    }

    private function getResourceLid(): ?string
    {
        $request = $this->requestStack->getMainRequest();
        Assertion::isInstanceOf($request, Request::class);
        $requestPrimaryData = $this->getRequestPrimaryData();
        $this->requestValidator->assertResourceLidIsValid($requestPrimaryData);

        return $requestPrimaryData['lid'] ?? null;
    }
}
