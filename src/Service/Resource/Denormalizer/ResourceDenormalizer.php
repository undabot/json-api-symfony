<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Denormalizer;

use Assert\Assertion;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Throwable;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\FlatResource;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\Exception\MissingDataValueResourceDenormalizationException;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\Exception\ResourceDenormalizationException;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\Definition\ResourceMetadataFactoryInterface;

class ResourceDenormalizer
{
    /** @var ResourceMetadataFactoryInterface */
    private $metadataFactory;

    /** @var DenormalizerInterface */
    private $denormalizer;

    public function __construct(ResourceMetadataFactoryInterface $metadataFactory, DenormalizerInterface $denormalizer)
    {
        $this->metadataFactory = $metadataFactory;
        $this->denormalizer = $denormalizer;
    }

    /**
     * Creates new instance of $class and populates it with values from the provided $resource.
     *
     * @throws MissingDataValueResourceDenormalizationException
     * @throws ResourceDenormalizationException
     * @throws \Assert\AssertionFailedException
     */
    public function denormalize(ResourceInterface $resource, string $class): ApiModel
    {
        Assertion::classExists($class);
        Assertion::subclassOf(
            $class,
            ApiModel::class,
            sprintf('%s is not instance of %s', $class, ApiModel::class)
        );
        $data = $this->prepareData($resource, $class);

        try {
            /** @var ApiModel $result */
            $result = $this->denormalizer->denormalize($data, $class, null, [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ]);
        } catch (MissingConstructorArgumentsException $e) {
            throw new MissingDataValueResourceDenormalizationException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        } catch (Throwable $e) {
            throw new ResourceDenormalizationException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $result;
    }

    /**
     * Prepares data for the incoming resource as key - value map.
     * For properties that are aliased (i.e. class property name is not the same as resource attribute / relationship)
     * change the key to match class property name.
     *
     * @return array<string, null|string|string[]>
     */
    private function prepareData(ResourceInterface $resource, string $class): array
    {
        $flatResource = new FlatResource($resource);

        $data = [
            'id' => $resource->getId(),
        ];
        $data = array_merge($data, $flatResource->getAttributes());
        $data = array_merge($data, $flatResource->getRelationships());

        /*
         * Resource has attribute and relationship names that can be different from the property name in the class.
         * See:
         * \Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation\Attribute::$name and
         * \Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation\Relationship::$name
        */
        $metadata = $this->metadataFactory->getClassMetadata($class);
        $aliasMap = [];
        $aliasMap = array_merge($aliasMap, $metadata->getAttributesAliasMap());
        $aliasMap = array_merge($aliasMap, $metadata->getRelationshipsAliasMap());

        foreach ($aliasMap as $alias => $propertyName) {
            if (false === \array_key_exists($alias, $data)) {
                continue;
            }

            $data[$propertyName] = $data[$alias];
            unset($data[$alias]);
        }

        return $data;
    }
}
