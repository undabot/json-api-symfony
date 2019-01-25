<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Encoding\PhpArray\Decode\AttributeCollectionJsonDecoder;
use Undabot\JsonApi\Encoding\PhpArray\Decode\LinkCollectionJsonDecoder;
use Undabot\JsonApi\Encoding\PhpArray\Decode\MetaJsonDecoder;
use Undabot\JsonApi\Encoding\PhpArray\Decode\RelationshipCollectionJsonDecoder;
use Undabot\JsonApi\Encoding\PhpArray\Decode\ResourceJsonDecoder;
use Undabot\JsonApi\Model\Resource\Relationship\Data\ToOneRelationshipData;
use Undabot\JsonApi\Model\Resource\Relationship\Relationship;
use Undabot\JsonApi\Model\Resource\Resource;
use Undabot\JsonApi\Model\Resource\ResourceIdentifier;

class CreateResourceFromJsonInputTest extends TestCase
{
    /** @var ResourceJsonDecoder */
    private $decoder;

    protected function setUp()
    {
        $linksDecoder = new LinkCollectionJsonDecoder();
        $metaDecoder = new MetaJsonDecoder();

        $this->decoder = new ResourceJsonDecoder(
            new RelationshipCollectionJsonDecoder($metaDecoder, $linksDecoder),
            new AttributeCollectionJsonDecoder(),
            $linksDecoder,
            $metaDecoder
        );
    }

    public function testCreateSimpleResourceFromJson()
    {
        $resourceJson = <<<JSON
{
    "type": "product",
    "id": "1",
    "attributes": {
        "name": "Rails is Omakase",
        "price": 1500
    },
    "relationships": {
        "category": {
            "data": {
                "type": "category",
                "id": "1"
            }
        }
    }
}
JSON;

        $resource = $this->decoder->decode(json_decode($resourceJson, true));
        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertNull($resource->getSelfUrl());
        $this->assertNull($resource->getMeta());
        $this->assertSame('product', $resource->getType());
        $this->assertSame('1', $resource->getId());

        $this->assertCount(2, $resource->getAttributes());

        $attributes = iterator_to_array($resource->getAttributes()->getIterator());
        $this->assertSame('name', $attributes[0]->getName());
        $this->assertSame('Rails is Omakase', $attributes[0]->getValue());
        $this->assertSame('price', $attributes[1]->getName());
        $this->assertSame(1500, $attributes[1]->getValue());

        $this->assertCount(1, $resource->getRelationships());
        $relationships = iterator_to_array($resource->getRelationships()->getIterator());
        /** @var Relationship $category */
        $category = $relationships[0];
        $this->assertSame('category', $category->getName());

        $categoryRelData = $category->getData();
        $this->assertInstanceOf(ToOneRelationshipData::class, $categoryRelData);
        $this->assertFalse($categoryRelData->isEmpty());

        /** @var ResourceIdentifier $categoryRelDataResourceIdentifier */
        $categoryRelDataResourceIdentifier = $categoryRelData->getData();
        $this->assertInstanceOf(ResourceIdentifier::class, $categoryRelDataResourceIdentifier);

        $this->assertSame('1', $categoryRelDataResourceIdentifier->getId());
        $this->assertSame('category', $categoryRelDataResourceIdentifier->getType());
        $this->assertNull($categoryRelDataResourceIdentifier->getMeta());
    }
}
