<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Controller;

use App\Infrastructure\Service\Bus\CommandBus;
use App\Infrastructure\Service\Bus\QueryBus;
use Assert\Assertion;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Http\Exception\Request\ResourceValidationException;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Http\Service\Factory\JsonApiRequestFactory;
use Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\MappedModelEncoder;
use Undabot\SymfonyJsonApi\Http\Service\Responder\ModelResponder;
use Undabot\SymfonyJsonApi\Http\Service\Responder\ObjectCollectionResponder;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollectionInterface;
use Undabot\SymfonyJsonApi\Model\Resource\CombinedResource;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\ResourceDenormalizer;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidator;

class AbstractResourceController
{
    /** @var JsonApiRequestFactory */
    protected $requestFactory;

    /** @var ResourceDenormalizer */
    protected $denormalizer;

    /** @var ResourceValidator */
    protected $validator;

    /** @var CommandBus */
    protected $commandBus;

    /** @var QueryBus */
    protected $queryBus;

    /** @var ModelResponder */
    protected $modelResponder;

    /** @var ObjectCollectionResponder */
    protected $objectCollectionResponder;

    /** @var MappedModelEncoder */
    protected $modelEncoder;

    public function __construct(
        JsonApiRequestFactory $requestFactory,
        ResourceDenormalizer $denormalizer,
        ResourceValidator $validator,
        CommandBus $commandBus,
        QueryBus $queryBus,
        ModelResponder $modelResponder,
        ObjectCollectionResponder $objectCollectionResponder,
        MappedModelEncoder $modelEncoder
    ) {
        $this->requestFactory = $requestFactory;
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->modelResponder = $modelResponder;
        $this->objectCollectionResponder = $objectCollectionResponder;
        $this->modelEncoder = $modelEncoder;
    }

    /**
     * @throws Exception
     */
    private function validateAndDenormalizeResource(ResourceInterface $resource, string $class)
    {
        $violations = $this->validator->validate($resource, $class);
        if (0 !== $violations->count()) {
            throw new ResourceValidationException($violations);
        }

        return $this->denormalizer->denormalize($resource, $class);
    }

    /**
     * @throws Exception
     */
    protected function constructCreateModel(string $class, Request $request, string $id = null)
    {
        Assertion::classExists($class);

        if (null !== $id) {
            $request = $this->requestFactory->makeCreateResourceRequestWithServerGeneratedId($request, $id);
        } else {
            $request = $this->requestFactory->makeCreateResourceRequest($request, true);
        }

        return $this->validateAndDenormalizeResource($request->getResource(), $class);
    }

    /**
     * @throws Exception
     */
    protected function constructUpdateModel(string $class, Request $request, ResourceInterface $baseResource)
    {
        Assertion::classExists($class);
        $request = $this->requestFactory->makeUpdateResourceRequest($request, $baseResource->getId());
        $combinedResource = new CombinedResource($baseResource, $request->getResource());

        return $this->validateAndDenormalizeResource($combinedResource, $class);
    }

    /**
     * @param $data array|ObjectCollectionInterface|mixed
     * @param array|null $includedModels
     * @param array|null $meta
     * @param array|null $links
     * @return ResourceCollectionResponse|ResourceResponse
     * @throws Exception
     */
    protected function jsonApiResponse($data, array $includedModels = null, array $meta = null, array $links = null)
    {
        if ($data instanceof ObjectCollectionInterface) {
            return $this->objectCollectionResponder->resourceCollectionResponse($data, $includedModels, $meta, $links);
        }

        if (true === is_array($data)) {
            return $this->modelResponder->resourceCollectionResponse($data, $includedModels, $meta, $links);
        }

        return $this->modelResponder->resourceResponse($data, $includedModels, $meta, $links);
    }

    /**
     * @throws Exception
     */
    protected function resourceCreatedResponse(
        $entity,
        array $includedModels = null,
        array $meta = null,
        array $links = null
    ): ResourceCreatedResponse {
        return $this->modelResponder->resourceCreatedResponse($entity, $includedModels, $meta, $links);
    }

    /**
     * @throws Exception
     */
    protected function resourceUpdatedResponse(
        $entity,
        array $includedModels = null,
        array $meta = null,
        array $links = null
    ): ResourceUpdatedResponse {
        return $this->modelResponder->resourceUpdatedResponse($entity, $includedModels, $meta, $links);
    }

    /**
     * @throws Exception
     */
    protected function encodeModel($model): ResourceInterface
    {
        return $this->modelEncoder->encodeModel($model);
    }

    /**
     * @throws Exception
     */
    protected function encodeModels(array $models)
    {
        return array_map([$this, 'encodeModel'], $models);
    }

}
