<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service;

use Assert\Assertion;
use Undabot\JsonApi\Model\Request\ResourcePayloadRequest;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
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
        Assertion::isInstanceOf($class, ApiModel::class, 'Given class is not instance of ApiModel');

        $resource = $request->getResource();
        $this->validator->assertValid($resource, $class);
        $model = $this->denormalizer->denormalize($resource, $class);

        return $model;
    }

    public function getModelFromResource(ResourceInterface $resource, string $class)
    {
        Assertion::isInstanceOf($class, ApiModel::class, 'Given class is not instance of ApiModel');

        $this->validator->assertValid($resource, $class);
        $model = $this->denormalizer->denormalize($resource, $class);

        return $model;
    }

}
