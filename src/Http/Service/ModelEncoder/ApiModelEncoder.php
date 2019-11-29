<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ModelEncoder;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Exception;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceFactory;

final class ApiModelEncoder implements EncoderInterface
{
    /** @var ResourceFactory */
    private $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory)
    {
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * Converts given entity first to the JSON:API resource model class by using provided $modelTransformer callable,
     * and then to the ResourceInterface by using ResourceFactory.
     *
     * @param mixed $data
     *
     * @throws Exception
     * @throws AssertionFailedException
     */
    public function encodeData($data, callable $modelTransformer): ResourceInterface
    {
        $apiModel = $modelTransformer($data);
        Assertion::isInstanceOf(
            $apiModel,
            ApiModel::class,
            sprintf('Invalid data conversion occurred. Expected instance of ApiModel, got %s', \get_class($apiModel))
        );

        return $this->resourceFactory->make($apiModel);
    }

    /**
     * Converts given entities first to JSON:API resource model classes by using provided $modelTransformer callable,
     * and then to the ResourceInterface instances by using ResourceFactory.
     *
     * @param mixed[] $dataset
     *
     * @return ResourceInterface[]
     */
    public function encodeDataset(array $dataset, callable $modelTransformer): array
    {
        return array_map(
            function ($resource) use ($modelTransformer) {
                return $this->encodeData($resource, $modelTransformer);
            },
            $dataset
        );
    }
}
