<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ModelEncoder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Proxy\Proxy;
use Exception;
use RuntimeException;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

final class MappedModelEncoder
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ModelEncoder */
    protected $modelEncoder;

    /** @var array */
    private $dataTransformersMap = [];

    public function __construct(EntityManagerInterface $entityManager, ModelEncoder $modelEncoder)
    {
        $this->entityManager = $entityManager;
        $this->modelEncoder = $modelEncoder;
    }

    private function getDataTransformer($model): callable
    {
        $modelClass = get_class($model);

        // Support Doctrine Entities that are usually represented as Proxy classes.
        // Resolve exact class name before looking up in the encoders map.
        if ($model instanceof Proxy) {
            $modelClass = $this->entityManager->getClassMetadata($modelClass)->name;
        }

        $encoder = $this->dataTransformersMap[$modelClass] ?? null;
        if (null === $encoder) {
            throw new RuntimeException(sprintf('Unsupported class `%s` given', $modelClass));
        }

        return $encoder;
    }

    /**
     * @throws Exception
     */
    public function encodeModel($data): ResourceInterface
    {
        $dataTransformer = $this->getDataTransformer($data);

        return $this->modelEncoder->encodeModel($data, $dataTransformer);
    }

    /**
     * @throws Exception
     */
    public function encodeModels(array $models): array
    {
        return array_map([$this, 'encodeModel'], $models);
    }

    public function addEncoder(string $modelClass, callable $encoder): void
    {
        $this->dataTransformersMap[$modelClass] = $encoder;
    }

    public function addEncodingMap(ModelEncoderMapInterface $encoderMap): void
    {
        foreach ($encoderMap->getMap() as $modelClass => $encoder) {
            $this->addEncoder($modelClass, $encoder);
        }
    }
}
