<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Factory;

use Undabot\JsonApi\Model\Link\Link;
use Undabot\JsonApi\Model\Link\LinkInterface;
use Undabot\JsonApi\Model\Link\LinkUrl;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollectionInterface;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceIdentifier;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

abstract class AbstractEntityToResourceFactory implements EntityToResourceFactoryInterface
{
    private $entityRelationshipFactory;

    public function __construct(EntityRelationshipFactory $entityRelationshipFactory)
    {
        $this->entityRelationshipFactory = $entityRelationshipFactory;
    }

    protected function makeAttributes(array $attributes): AttributeCollectionInterface
    {
        $resourceAttributes = [];

        foreach ($attributes as $attributeName => $attributeValue) {
            $resourceAttributes[] = new Attribute($attributeName, $attributeValue);
        }

        return new AttributeCollection($resourceAttributes);
    }

    protected function buildResourceIdentifier(ResourceInterface $resource): ResourceIdentifierInterface
    {
        return new ResourceIdentifier($resource->getId(), $resource->getType());
    }

    protected function makeRelationships(array $relationships): RelationshipCollectionInterface
    {
        return $this->entityRelationshipFactory->makeRelationships($relationships);
    }

    protected function makeSelfLink(string $url): LinkInterface
    {
        return new Link('self', new LinkUrl($url));
    }

    protected function makeMeta(array $meta): Meta
    {
        return new Meta($meta);
    }

    abstract public function create($entity): ResourceInterface;

    abstract public function createIdentifier($entity): ResourceIdentifierInterface;
}
