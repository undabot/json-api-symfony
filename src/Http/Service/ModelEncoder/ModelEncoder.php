<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ModelEncoder;

use Assert\Assertion;
use Exception;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceFactory;

final class ModelEncoder
{
    /** @var ResourceFactory */
    protected $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory)
    {
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Converts given entity first to the JSON:API resource model class by using provided $modelTransformer callable,
     * and then to the ResourceInterface by using ResourceFactory
     *
     * @throws Exception
     */
    public function encodeModel($data, callable $modelTransformer): ResourceInterface
    {
        $apiModel = $modelTransformer($data);

        return $this->resourceFactory->make($apiModel);
    }

    /**
     * Converts given entities first to JSON:API resource model classes by using provided $modelTransformer callable,
     * and then to the ResourceInterface instances by using ResourceFactory
     *
     * @param ApiModel[] $models
     * @throws Exception
     */
    public function encodeModels(array $models, callable $modelTransformer): array
    {
        Assertion::allIsInstanceOf($models, ApiModel::class);

        return array_map(
            function ($resource) use ($modelTransformer) {
                return $this->encodeModel($resource, $modelTransformer);
            },
            $models
        );
    }
}
