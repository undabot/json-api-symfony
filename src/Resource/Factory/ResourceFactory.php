<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Factory;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\Resource;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\Attribute\ResourceAttributesFactory;
use Undabot\SymfonyJsonApi\Model\Relationship\ResourceRelationshipsFactory;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\ResourceMetadata;

class ResourceFactory
{
    /** @var ResourceMetadataFactory */
    private $metadataFactory;

    public function __construct(ResourceMetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function make($resource): ResourceInterface
    {
        $metadata = $this->metadataFactory->getResourceMetadata($resource);

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();

        $id = $propertyAccessor->getValue($resource, 'id');
        $type = $metadata->getType();
        if (null === $type) {
            $type = $propertyAccessor->getValue($resource, 'type');
        }

        $attributes = $this->makeAttributeCollection($resource, $metadata);
        $relationships = $this->makeRelationshipsCollection($resource, $metadata);
        $resource = new Resource($id, $type, $attributes, $relationships);

        return $resource;
    }

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

        $attributeBuilder = ResourceAttributesFactory::make();

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

        $relationshipBuilder = ResourceRelationshipsFactory::make();

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
