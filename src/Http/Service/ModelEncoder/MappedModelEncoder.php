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
    private $encodersMap = [];

    public function __construct(EntityManagerInterface $entityManager, ModelEncoder $modelEncoder)
    {
        $this->entityManager = $entityManager;
        $this->modelEncoder = $modelEncoder;
    }

    private function getModelEncoder($model): callable
    {
        $modelClass = get_class($model);
        if ($model instanceof Proxy) {
            $modelClass = $this->entityManager->getClassMetadata($modelClass)->name;
        }

        $transformer = $this->encodersMap[$modelClass] ?? null;
        if (null === $transformer) {
            throw new RuntimeException(sprintf('Unsupported class `%s` given', $modelClass));

        }

        return $transformer;
    }

    /**
     * @throws Exception
     */
    public function encodeModel($model): ResourceInterface
    {
        $modelEncoder = $this->getModelEncoder($model);

        return $this->modelEncoder->encodeModel($model, $modelEncoder);
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
        $this->encodersMap[$modelClass] = $encoder;
    }

    public function addEncodingMap(ModelEncoderMapInterface $encoderMap): void
    {
        foreach ($encoderMap->getMap() as $modelClass => $encoder) {
            $this->addEncoder($modelClass, $encoder);
        }
    }
}
