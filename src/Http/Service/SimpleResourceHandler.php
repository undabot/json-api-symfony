<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service;

use Undabot\JsonApi\Definition\Model\Request\ResourcePayloadRequest;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\ResourceDenormalizer;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidator;

final class SimpleResourceHandler
{
    /** @var ResourceValidator */
    private $validator;

    /** @var ResourceDenormalizer */
    private $denormalizer;

    public function __construct(ResourceValidator $validator, ResourceDenormalizer $denormalizer)
    {
        $this->validator = $validator;
        $this->denormalizer = $denormalizer;
    }

    public function getModelFromRequest(ResourcePayloadRequest $request, string $class): ApiModel
    {
        return $this->getModelFromResource($request->getResource(), $class);
    }

    /**
     * @throws \Exception
     */
    public function getModelFromResource(ResourceInterface $resource, string $class): ApiModel
    {
        if (false === is_subclass_of($class, ApiModel::class)) {
            throw new \InvalidArgumentException('Given class is not instance of ApiModel');
        }

        $this->validator->assertValid($resource, $class);

        return $this->denormalizer->denormalize($resource, $class);
    }
}
