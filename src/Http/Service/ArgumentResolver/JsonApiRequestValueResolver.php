<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ArgumentResolver;

use Assert\AssertionFailedException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Undabot\JsonApi\Definition\Exception\Request\ClientGeneratedIdIsNotAllowedException;
use Undabot\JsonApi\Definition\Exception\Request\RequestException;
use Undabot\JsonApi\Definition\Model\Request\CreateResourceRequestInterface;
use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\JsonApi\Definition\Model\Request\GetResourceRequestInterface;
use Undabot\JsonApi\Definition\Model\Request\UpdateResourceRequestInterface;
use Undabot\JsonApi\Implementation\Encoding\Exception\JsonApiEncodingException;
use Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory;

/**
 * Resolves JSON:API request controller arguments from the current HTTP request.
 *
 * Replaces the former Sensio FrameworkExtraBundle param converter. Options that were
 * previously passed via the `@ParamConverter` annotation are now provided with the
 * #[JsonApiRequestOptions] argument attribute.
 */
final readonly class JsonApiRequestValueResolver implements ValueResolverInterface
{
    private const array SUPPORTED_CLASSES = [
        GetResourceCollectionRequestInterface::class,
        GetResourceRequestInterface::class,
        CreateResourceRequestInterface::class,
        UpdateResourceRequestInterface::class,
    ];

    public function __construct(private RequestFactory $requestFactory) {}

    /**
     * @return iterable<CreateResourceRequestInterface|GetResourceCollectionRequestInterface|GetResourceRequestInterface|UpdateResourceRequestInterface>
     *
     * @throws AssertionFailedException
     * @throws JsonApiEncodingException
     * @throws RequestException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $class = $argument->getType();

        if (null === $class || false === \in_array($class, self::SUPPORTED_CLASSES, true)) {
            return [];
        }

        $options = $argument->getAttributesOfType(JsonApiRequestOptions::class)[0] ?? new JsonApiRequestOptions();

        if (GetResourceCollectionRequestInterface::class === $class) {
            return [$this->requestFactory->getResourceCollectionRequest($request)];
        }

        if (GetResourceRequestInterface::class === $class) {
            $resourceId = $this->getResourceId($request, $options);

            return null === $resourceId ? [] : [$this->requestFactory->getResourceRequest($request, $resourceId)];
        }

        if (CreateResourceRequestInterface::class === $class) {
            return [$this->createResourceRequest($request, $options)];
        }

        $resourceId = $this->getResourceId($request, $options);

        return null === $resourceId ? [] : [$this->requestFactory->updateResourceRequest($request, $resourceId)];
    }

    /**
     * @throws AssertionFailedException
     * @throws JsonApiEncodingException
     * @throws RequestException
     */
    private function createResourceRequest(
        Request $request,
        JsonApiRequestOptions $options,
    ): CreateResourceRequestInterface {
        $id = null;

        if (
            $this->requestFactory->requestResourceHasClientSideGeneratedId($request)
            && false === $options->clientGeneratedIds
        ) {
            throw new ClientGeneratedIdIsNotAllowedException();
        }

        if (false === $options->clientGeneratedIds) {
            /** @todo allow devs to choose ID generation strategy */
            $id = (string) Uuid::uuid4();
        }

        return $this->requestFactory->createResourceRequest($request, $id);
    }

    private function getResourceId(Request $request, JsonApiRequestOptions $options): ?string
    {
        return $request->attributes->get('_route_params')[$options->idAttribute] ?? null;
    }
}
