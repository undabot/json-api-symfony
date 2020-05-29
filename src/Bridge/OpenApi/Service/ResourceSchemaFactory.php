<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Service;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\RelationshipSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\CreateSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\IdentifierSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ReadSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\UpdateSchema;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\AttributeMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\RelationshipMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;

class ResourceSchemaFactory
{
    /** @var ResourceMetadataFactory */
    private $resourceMetadataFactory;

    /** @var AttributeSchemaFactory */
    private $attributeSchemaFactory;

    /** @var RelationshipSchemaFactory */
    private $relationshipSchemaFactory;

    public function __construct(
        ResourceMetadataFactory $resourceMetadataFactory,
        AttributeSchemaFactory $attributeSchemaFactory,
        RelationshipSchemaFactory $relationshipSchemaFactory
    ) {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->attributeSchemaFactory = $attributeSchemaFactory;
        $this->relationshipSchemaFactory = $relationshipSchemaFactory;
    }

    /**
     * @throws \Exception
     */
    public function identifier(string $resourceClass): IdentifierSchema
    {
        $resourceMetadata = $this->resourceMetadataFactory->getClassMetadata($resourceClass);

        return new IdentifierSchema($resourceMetadata->getType());
    }

    /**
     * @throws \Exception
     */
    public function readSchema(string $resourceClass): ReadSchema
    {
        $resourceMetadata = $this->resourceMetadataFactory->getClassMetadata($resourceClass);

        return new ReadSchema(
            $resourceMetadata->getType(),
            $this->getAttributes($resourceMetadata),
            $this->getRelationships($resourceMetadata)
        );
    }

    /**
     * Returns array of schemas representing each resource identifier contained in the relationship.
     *
     * @throws \Exception
     *
     * @return IdentifierSchema[]
     */
    public function relationshipsIdentifiers(string $resourceClass): array
    {
        $resourceMetadata = $this->resourceMetadataFactory->getClassMetadata($resourceClass);

        /** @var RelationshipMetadata[] $relationshipsMetadata */
        $relationshipsMetadata = $resourceMetadata->getRelationshipsMetadata()->toArray();

        $identifierSchemas = [];
        /** @var RelationshipMetadata $relationshipMetadata */
        foreach ($relationshipsMetadata as $relationshipMetadata) {
            $identifierSchemas[] = new IdentifierSchema($relationshipMetadata->getRelatedResourceType());
        }

        return $identifierSchemas;
    }

    /**
     * @throws \Exception
     */
    public function createSchema(string $resourceClass): CreateSchema
    {
        $resourceMetadata = $this->resourceMetadataFactory->getClassMetadata($resourceClass);

        return new CreateSchema(
            $resourceMetadata->getType(),
            $this->getAttributes($resourceMetadata),
            $this->getRelationships($resourceMetadata)
        );
    }

    /**
     * @throws \Exception
     */
    public function updateSchema(string $resourceClass): UpdateSchema
    {
        $resourceMetadata = $this->resourceMetadataFactory->getClassMetadata($resourceClass);

        return new UpdateSchema(
            $resourceMetadata->getType(),
            $this->getAttributes($resourceMetadata),
            $this->getRelationships($resourceMetadata)
        );
    }

    /**
     * @return AttributeSchema[]
     */
    private function getAttributes(ResourceMetadata $metadata): array
    {
        return $metadata->getAttributesMetadata()
            ->map(function (AttributeMetadata $attributeMetadata) {
                return $this->attributeSchemaFactory->make($attributeMetadata);
            })
            ->toArray();
    }

    /**
     * @return RelationshipSchema[]
     */
    private function getRelationships(ResourceMetadata $metadata): array
    {
        return $metadata->getRelationshipsMetadata()
            ->map(function (RelationshipMetadata $relationshipMetadata) {
                return $this->relationshipSchemaFactory->make($relationshipMetadata);
            })
            ->toArray();
    }
}
