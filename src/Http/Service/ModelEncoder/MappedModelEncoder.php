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

    /**
     * Returns factory callable that will transform given $data object to ApiModel by following defined
     * encoding rules (encoding map).
     *
     * Supports resolving Doctrine proxy classes to actuall entity class names.
     *
     * @see \Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\ModelEncoderMapInterface
     */
    private function getDataTransformer($data): callable
    {
        $dataClass = get_class($data);

        // Support Doctrine Entities that are usually represented as Proxy classes.
        // Resolve exact class name before looking up in the encoders map.
        if ($data instanceof Proxy) {
            $dataClass = $this->entityManager->getClassMetadata($dataClass)->name;
        }

        $encoder = $this->dataTransformersMap[$dataClass] ?? null;
        if (null === $encoder) {
            $message = sprintf(
                'Couldn\'t resolve transformer class for object of class `%s` given. Have you defined data transformer for that data class?',
                $dataClass);
            throw new RuntimeException($message);
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
    public function encodeModels(array $data): array
    {
        return array_map([$this, 'encodeModel'], $data);
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
