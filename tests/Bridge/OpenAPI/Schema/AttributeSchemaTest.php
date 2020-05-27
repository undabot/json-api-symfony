<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Schema;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class AttributeSchemaTest extends TestCase
{
    public function testItCanBeConstructedWithMinimalData(): void
    {
        $schema = new AttributeSchema('name', 'type', false, null, null, null);
        static::assertSame(
            $schema->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'type',
                'nullable' => false,
            ]
        );

        $schema2 = new AttributeSchema('name', 'type', true, null, null, null);
        static::assertSame(
            $schema2->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'type',
                'nullable' => true,
            ]
        );
    }

    public function testItCanBeConstructedWithDescription(): void
    {
        $schema = new AttributeSchema(
            'name',
            'type',
            false,
            'Some description that is a little bit longer',
            null,
            null
        );

        static::assertSame(
            $schema->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'type',
                'nullable' => false,
                'description' => 'Some description that is a little bit longer',
            ]
        );
    }

    public function testItCanBeConstructedWithDescriptionAndFormat(): void
    {
        $schema = new AttributeSchema(
            'name',
            'type',
            false,
            'Some description that is a little bit longer',
            'numeric',
            null
        );

        static::assertSame(
            $schema->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'type',
                'nullable' => false,
                'description' => 'Some description that is a little bit longer',
                'format' => 'numeric',
            ]
        );
    }

    public function testItCanBeConstructedWithDescriptionAndFormatAndExample(): void
    {
        $schema = new AttributeSchema(
            'name',
            'type',
            false,
            'Some description that is a little bit longer',
            'numeric',
            'example'
        );

        static::assertSame(
            $schema->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'type',
                'nullable' => false,
                'description' => 'Some description that is a little bit longer',
                'example' => 'example',
                'format' => 'numeric',
            ]
        );
    }

    public function testItWillConvertTypeToOpenApi(): void
    {
        $schema = new AttributeSchema(
            'name',
            'int',
            false,
            'Some description that is a little bit longer',
            'numeric',
            'example'
        );

        static::assertSame(
            $schema->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'integer',
                'nullable' => false,
                'description' => 'Some description that is a little bit longer',
                'example' => 'example',
                'format' => 'numeric',
            ]
        );
    }
}
