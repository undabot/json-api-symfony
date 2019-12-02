<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Implementation\Encoding\PhpArrayToAttributeCollectionEncoder;
use Undabot\JsonApi\Implementation\Encoding\PhpArrayToLinkCollectionEncoder;
use Undabot\JsonApi\Implementation\Encoding\PhpArrayToMetaEncoder;
use Undabot\JsonApi\Implementation\Encoding\PhpArrayToRelationshipCollectionEncoder;
use Undabot\JsonApi\Implementation\Encoding\PhpArrayToResourceEncoder;
use Undabot\JsonApi\Implementation\Model\Resource\Relationship\Data\ToOneRelationshipData;
use Undabot\JsonApi\Implementation\Model\Resource\Relationship\Relationship;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceIdentifier;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class CreateResourceFromJsonInputTest extends TestCase
{
    /** @var PhpArrayToResourceEncoder */
    private $phpArrayToResourceEncoder;

    protected function setUp(): void
    {
        $phpArrayToLinkCollectionEncoder = new PhpArrayToLinkCollectionEncoder();
        $phpArrayToMetaEncoder = new PhpArrayToMetaEncoder();

        $this->phpArrayToResourceEncoder = new PhpArrayToResourceEncoder(
            new PhpArrayToRelationshipCollectionEncoder($phpArrayToMetaEncoder, $phpArrayToLinkCollectionEncoder),
            new PhpArrayToAttributeCollectionEncoder(),
            $phpArrayToMetaEncoder
        );
    }

    public function testCreateSimpleResourceFromJson(): void
    {
        $resourceJson = <<<'JSON'
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

        $resource = $this->phpArrayToResourceEncoder->decode(json_decode($resourceJson, true));
        static::assertInstanceOf(Resource::class, $resource);
        static::assertNull($resource->getSelfUrl());
        static::assertNull($resource->getMeta());
        static::assertSame('product', $resource->getType());
        static::assertSame('1', $resource->getId());

        static::assertCount(2, $resource->getAttributes());

        $attributes = iterator_to_array($resource->getAttributes()->getIterator());
        static::assertSame('name', $attributes[0]->getName());
        static::assertSame('Rails is Omakase', $attributes[0]->getValue());
        static::assertSame('price', $attributes[1]->getName());
        static::assertSame(1500, $attributes[1]->getValue());

        static::assertCount(1, $resource->getRelationships());
        $relationships = iterator_to_array($resource->getRelationships()->getIterator());
        /** @var Relationship $category */
        $category = $relationships[0];
        static::assertSame('category', $category->getName());

        $categoryRelData = $category->getData();
        static::assertInstanceOf(ToOneRelationshipData::class, $categoryRelData);
        static::assertFalse($categoryRelData->isEmpty());

        /** @var ResourceIdentifier $categoryRelDataResourceIdentifier */
        $categoryRelDataResourceIdentifier = $categoryRelData->getData();
        static::assertInstanceOf(ResourceIdentifier::class, $categoryRelDataResourceIdentifier);

        static::assertSame('1', $categoryRelDataResourceIdentifier->getId());
        static::assertSame('category', $categoryRelDataResourceIdentifier->getType());
        static::assertNull($categoryRelDataResourceIdentifier->getMeta());
    }
}
