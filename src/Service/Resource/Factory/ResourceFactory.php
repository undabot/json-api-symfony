<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Factory;

use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Implementation\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceAttributesBuilder;
use Undabot\SymfonyJsonApi\Service\Resource\Builder\ResourceRelationshipsBuilder;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidator;

/**
 * Class responsible for creating a ResourceInterface instance out of given API model that is
 * correctly annotated with library's annotations.
 */
class ResourceFactory
{
    /** @psalm-suppress PossiblyUnusedMethod */
    public function __construct(
        private ResourceMetadataFactory $metadataFactory,
        private bool $shouldValidateReadModel,
        private ResourceValidator $validator,
    ) {}

    /**
     * @throws AnnotationException
     * @throws \ReflectionException
     * @throws InvalidResourceMappingException
     */
    public function make(ApiModel $apiModel): ResourceInterface
    {
        $metadata = $this->metadataFactory->getInstanceMetadata($apiModel);

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
        $id = $propertyAccessor->getValue($apiModel, 'id');
        if (!\is_string($id)) {
            throw new \InvalidArgumentException('ID must be a string.');
        }
        $type = $metadata->getType();

        $attributes = $this->makeAttributeCollection($apiModel, $metadata);
        $relationships = $this->makeRelationshipsCollection($apiModel, $metadata);

        $resource = new Resource($id, $type, $attributes, $relationships);
        if (true === $this->shouldValidateReadModel) {
            $this->validator->assertValid($resource, \get_class($apiModel));
        }

        return $resource;
    }

    private function makeAttributeCollection(ApiModel $apiModel, ResourceMetadata $metadata): ?AttributeCollection
    {
        // @phpstan-ignore-next-line
        if (empty($metadata->getAttributesMetadata())) {
            return null;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();

        $attributeBuilder = ResourceAttributesBuilder::make();

        foreach ($metadata->getAttributesMetadata() as $attributesMetadatum) {
            $attributeBuilder->add(
                $attributesMetadatum->getName(),
                $propertyAccessor->getValue($apiModel, $attributesMetadatum->getPropertyPath())
            );
        }

        return $attributeBuilder->get();
    }

    private function makeRelationshipsCollection(
        ApiModel $apiModel,
        ResourceMetadata $metadata
    ): ?RelationshipCollection {
        // @phpstan-ignore-next-line
        if (empty($metadata->getRelationshipsMetadata())) {
            return null;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();

        $relationshipBuilder = ResourceRelationshipsBuilder::make();

        foreach ($metadata->getRelationshipsMetadata() as $relationshipsMetadatum) {
            $propertyValue = $propertyAccessor->getValue($apiModel, $relationshipsMetadatum->getPropertyPath());

            if ($relationshipsMetadatum->isToMany()) {
                if (!\is_array($propertyValue)) {
                    continue;
                }

                $ids = array_map('strval', $propertyValue);
                $relationshipBuilder->toMany(
                    $relationshipsMetadatum->getName(),
                    $relationshipsMetadatum->getRelatedResourceType(),
                    $ids
                );
            } else {
                if (!\is_string($propertyValue) && null !== $propertyValue) {
                    continue;
                }

                $id = null !== $propertyValue ? (string) $propertyValue : null; // Cast to string or null
                $relationshipBuilder->toOne(
                    $relationshipsMetadatum->getName(),
                    $relationshipsMetadatum->getRelatedResourceType(),
                    $id
                );
            }
        }

        return $relationshipBuilder->get();
    }
}
