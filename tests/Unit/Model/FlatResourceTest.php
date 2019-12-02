<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Model\Resource;

use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Implementation\Factory\RelationshipDataFactory;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Implementation\Model\Resource\Relationship\Relationship;
use Undabot\JsonApi\Implementation\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Model\Resource\FlatResource;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class FlatResourceTest extends TestCase
{
    public function testFlatResourceCorrectlyFlattensAttributes(): void
    {
        $resource = new Resource('id', 'resource', new AttributeCollection([
            new Attribute('attribute1', 'string'),
            new Attribute('attribute2', 1),
            new Attribute('attribute3', 2.0),
            new Attribute('attribute4', ['foo' => 'bar', 1, 2]),
            new Attribute('attribute5', null),
        ]));

        $flatResource = new FlatResource($resource);

        static::assertSame([
            'attribute1' => 'string',
            'attribute2' => 1,
            'attribute3' => 2.0,
            'attribute4' => ['foo' => 'bar', 1, 2],
            'attribute5' => null,
        ], $flatResource->getAttributes());
    }

    public function testFlatResourceCorrectlyFlattensRelationships(): void
    {
        $relationshipDataFactory = new RelationshipDataFactory();
        $resource = new Resource('id', 'resource', null, new RelationshipCollection([
            new Relationship('empty2many', null, $relationshipDataFactory->make('empty2many', true, [])),
            new Relationship('empty2one', null, $relationshipDataFactory->make('empty2one', false, null)),
            new Relationship('2many', null, $relationshipDataFactory->make('2many', true, ['1', '2', '3'])),
            new Relationship('2one', null, $relationshipDataFactory->make('2one', false, '4')),
        ]));

        $flatResource = new FlatResource($resource);

        static::assertSame([
            'empty2many' => [],
            'empty2one' => null,
            '2many' => ['1', '2', '3'],
            '2one' => '4',
        ], $flatResource->getRelationships());
    }
}
