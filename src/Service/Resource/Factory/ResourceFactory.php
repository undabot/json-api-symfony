<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Factory;

use Assert\Assertion;
use Doctrine\Common\Annotations\AnnotationException;
use ReflectionException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\Resource;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceRelationshipsBuilder;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceAttributesBuilder;

class ResourceFactory
{
    /** @var ResourceMetadataFactory */
    private $metadataFactory;

    public function __construct(ResourceMetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws InvalidResourceMappingException
     */
    public function make($resource): ResourceInterface
    {
        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();

        $id = $propertyAccessor->getValue($resource, 'id');
        $type = $metadata->getType();

        // If the resource type is not defined in the resource metadata, try to access the `type` property directly.
        // If the type property is not accessible, throw an exception since Resource without type definition cannot be created
        if (null === $type && true === $propertyAccessor->isReadable($resource, 'type')) {
            $type = $propertyAccessor->getValue($resource, 'type');
        }

        Assertion::notNull($type, 'Resource type cannot be inhered neither from the annotation nor `type` property');

        $attributes = $this->makeAttributeCollection($resource, $metadata);
        $relationships = $this->makeRelationshipsCollection($resource, $metadata);
        $resource = new Resource($id, $type, $attributes, $relationships);

        return $resource;
    }

    /**
     * @throws AnnotationException
     * @throws InvalidResourceMappingException
     * @throws ReflectionException
     */
    public function makeCollection(array $resources): ResourceCollectionInterface
    {
        $resourceObjects = [];

        foreach ($resources as $resource) {
            $resourceObjects[] = $this->make($resource);
        }

        return new ResourceCollection($resourceObjects);
    }

    private function makeAttributeCollection($resource, ResourceMetadata $metadata): ?AttributeCollection
    {
        if (true === empty($metadata->getAttributesMetadata())) {
            return null;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();

        $attributeBuilder = ResourceAttributesBuilder::make();

        foreach ($metadata->getAttributesMetadata() as $attributesMetadatum) {
            $attributeBuilder->add(
                $attributesMetadatum->getName(),
                $propertyAccessor->getValue($resource, $attributesMetadatum->getPropertyPath())
            );
        }

        return $attributeBuilder->get();
    }

    private function makeRelationshipsCollection($resource, ResourceMetadata $metadata): ?RelationshipCollection
    {
        if (true === empty($metadata->getRelationshipsMetadata())) {
            return null;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();

        $relationshipBuilder = ResourceRelationshipsBuilder::make();

        foreach ($metadata->getRelationshipsMetadata() as $relationshipsMetadatum) {

            if ($relationshipsMetadatum->isToMany()) {
                $relationshipBuilder->toMany(
                    $relationshipsMetadatum->getName(),
                    $relationshipsMetadatum->getRelatedResourceType(),
                    $propertyAccessor->getValue($resource, $relationshipsMetadatum->getPropertyPath())
                );
            } else {
                $relationshipBuilder->toOne(
                    $relationshipsMetadatum->getName(),
                    $relationshipsMetadatum->getRelatedResourceType(),
                    $propertyAccessor->getValue($resource, $relationshipsMetadatum->getPropertyPath())
                );
            }
        }

        return $relationshipBuilder->get();
    }
}
