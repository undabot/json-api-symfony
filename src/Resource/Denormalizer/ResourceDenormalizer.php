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
use Undabot\SymfonyJsonApi\Resource\FlatResource;
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
        $flatResource = new FlatResource($resource);
        $data = [];
        $data = array_merge($data, $flatResource->getAttributes());
        $data = array_merge($data, $flatResource->getRelationships());

        // Replace keys so that the key matches target object propert name.
        // This is needed to support aliased properties (i.e. different attribute/resource names from the property name)
        $metadata = $this->metadataFactory->getClassMetadata($class);
        $aliasMap = [];
        $aliasMap = array_merge($aliasMap, $metadata->getAttributesAliasMap());
        $aliasMap = array_merge($aliasMap, $metadata->getRelationshipsAliasMap());

        foreach ($aliasMap as $name => $property) {
            if (false === isset($data[$name])) {
                continue;
            }

            $data[$property] = $data[$name];
            unset($data[$name]);
        }

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
}
