<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\ParamConverter;

use Assert\AssertionFailedException;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Encoding\Exception\PhpArrayEncodingException;
use Undabot\JsonApi\Exception\Request\ClientGeneratedIdIsNotAllowedException;
use Undabot\JsonApi\Exception\Request\RequestException;
use Undabot\JsonApi\Model\Request\CreateResourceRequestInterface;
use Undabot\JsonApi\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\JsonApi\Model\Request\GetResourceRequestInterface;
use Undabot\JsonApi\Model\Request\UpdateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory;

class JsonApiRequestParamConverter implements ParamConverterInterface
{
    public const OPTION_CLIENT_GENERATED_IDS = 'clientGeneratedIds';

    /** @var RequestFactory */
    private $requestFactory;

    public function __construct(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * Stores the object in the request.
     *
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @throws RequestException
     * @throws AssertionFailedException
     * @throws PhpArrayEncodingException
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();
        $class = $configuration->getClass();

        if (GetResourceCollectionRequestInterface::class === $class) {
            $value = $this->requestFactory->getResourceCollectionRequest($request);
            $request->attributes->set($name, $value);

            return true;
        }

        if (GetResourceRequestInterface::class === $class) {
            $resourceId = $this->getResourceId($request, $configuration->getOptions());
            if (null !== $resourceId) {
                $value = $this->requestFactory->getResourceRequest($request, $resourceId);
                $request->attributes->set($name, $value);

                return true;
            }
        }

        if (CreateResourceRequestInterface::class === $class) {
            /** @var bool $useClientGeneratedIDs */
            $useClientGeneratedIDs = $configuration->getOptions()[self::OPTION_CLIENT_GENERATED_IDS] ?? false;
            $id = null;

            if (
                $this->requestFactory->requestResourceHasClientSideGeneratedId($request)
                && false === $useClientGeneratedIDs
            ) {
                throw new ClientGeneratedIdIsNotAllowedException();
            }

            if (false === $useClientGeneratedIDs) {
                /** @todo allow devs to choose ID generation strategy */
                $id = (string) Uuid::uuid4();
            }

            $value = $this->requestFactory->createResourceRequest($request, $id);
            $request->attributes->set($name, $value);

            return true;
        }

        if (UpdateResourceRequestInterface::class === $class) {
            $resourceId = $this->getResourceId($request, $configuration->getOptions());
            if (null !== $resourceId) {
                $value = $this->requestFactory->updateResourceRequest($request, $resourceId);
                $request->attributes->set($name, $value);

                return true;
            }
        }

        return false;
    }

    public function supports(ParamConverter $configuration)
    {
        $class = $configuration->getClass();

        $supportedClasses = [
            GetResourceCollectionRequestInterface::class,
            GetResourceRequestInterface::class,
            CreateResourceRequestInterface::class,
            UpdateResourceRequestInterface::class,
        ];

        return \in_array($class, $supportedClasses, true);
    }

    /**
     * @param mixed[] $options
     */
    private function getResourceId(Request $request, array $options): ?string
    {
        // Which route attribute contains the ID (URL path param)?
        $idAttribute = $options['id'] ?? 'id';

        return $request->attributes->get('_route_params')[$idAttribute] ?? null;
    }
}
