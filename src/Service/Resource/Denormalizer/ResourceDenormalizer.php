<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Denormalizer;

use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Throwable;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Resource\Denormalizer\Exception\MissingDataValueResourceDenormalizationException;
use Undabot\SymfonyJsonApi\Resource\Denormalizer\Exception\ResourceDenormalizationException;
use Undabot\SymfonyJsonApi\Resource\Model\FlatResource;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory\ResourceMetadataFactoryInterface;

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
     * Creates new instance of $class and populates it with values from the provided $resource
     *
     * @throws MissingDataValueResourceDenormalizationException
     * @throws ResourceDenormalizationException
     */
    public function denormalize(ResourceInterface $resource, string $class)
    {
        $data = $this->prepareData($resource, $class);

        try {
            $result = $this->denormalizer->denormalize($data, $class, null, [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ]);
        } catch (MissingConstructorArgumentsException $e) {
            throw new MissingDataValueResourceDenormalizationException(
                $e->getMessage(),
                $e->getCode(),
                $e->getPrevious()
            );
        } catch (Throwable $e) {
            throw new ResourceDenormalizationException(
                $e->getMessage(),
                $e->getCode(),
                $e->getPrevious()
            );
        }

        return $result;
    }

    /**
     * Prepares data forthe incoming resource as key - value map.
     * For properties that are aliased (i.e. class property name is not the same as resource attribute / relationship)
     * change the key to match class property name.
     */
    private function prepareData(ResourceInterface $resource, string $class): array
    {
        $flatResource = new FlatResource($resource);

        $data = [];
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
            if (false === isset($data[$alias])) {
                continue;
            }

            $data[$propertyName] = $data[$alias];
            unset($data[$alias]);
        }

        return $data;
    }
}
