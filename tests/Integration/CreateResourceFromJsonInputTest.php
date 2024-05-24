<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
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
 *
 * @coversNothing
 *
 * @small
 */
#[CoversNothing]
#[Small]
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
        self::assertInstanceOf(Resource::class, $resource);
        self::assertNull($resource->getSelfUrl());
        self::assertNull($resource->getMeta());
        self::assertSame('product', $resource->getType());
        self::assertSame('1', $resource->getId());

        self::assertCount(2, $resource->getAttributes());

        $attributes = iterator_to_array($resource->getAttributes()->getIterator());
        self::assertSame('name', $attributes[0]->getName());
        self::assertSame('Rails is Omakase', $attributes[0]->getValue());
        self::assertSame('price', $attributes[1]->getName());
        self::assertSame(1500, $attributes[1]->getValue());

        self::assertCount(1, $resource->getRelationships());
        $relationships = iterator_to_array($resource->getRelationships()->getIterator());

        /** @var Relationship $category */
        $category = $relationships[0];
        self::assertSame('category', $category->getName());

        $categoryRelData = $category->getData();
        self::assertInstanceOf(ToOneRelationshipData::class, $categoryRelData);
        self::assertFalse($categoryRelData->isEmpty());

        /** @var ResourceIdentifier $categoryRelDataResourceIdentifier */
        $categoryRelDataResourceIdentifier = $categoryRelData->getData();
        self::assertInstanceOf(ResourceIdentifier::class, $categoryRelDataResourceIdentifier);

        self::assertSame('1', $categoryRelDataResourceIdentifier->getId());
        self::assertSame('category', $categoryRelDataResourceIdentifier->getType());
        self::assertNull($categoryRelDataResourceIdentifier->getMeta());
    }
}
