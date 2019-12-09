<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Schema;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;

class AttributeSchemaTest extends TestCase
{
    public function testItCanBeConstructedWithMinimalData()
    {
        $schema = new AttributeSchema('name', 'type', false, null, null, null);
        $this->assertSame(
            $schema->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'type',
                'nullable' => false,
            ]
        );

        $schema2 = new AttributeSchema('name', 'type', true, null, null, null);
        $this->assertSame(
            $schema2->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'type',
                'nullable' => true,
            ]
        );
    }

    public function testItCanBeConstructedWithDescription()
    {
        $schema = new AttributeSchema(
            'name',
            'type',
            false,
            'Some description that is a little bit longer',
            null,
            null
        );

        $this->assertSame(
            $schema->toOpenApi(),
            [
                'title' => 'name',
                'type' => 'type',
                'nullable' => false,
                'description' => 'Some description that is a little bit longer',
            ]
        );
    }

    public function testItCanBeConstructedWithDescriptionAndFormat()
    {
        $schema = new AttributeSchema(
            'name',
            'type',
            false,
            'Some description that is a little bit longer',
            'numeric',
            null
        );

        $this->assertSame(
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

    public function testItCanBeConstructedWithDescriptionAndFormatAndExample()
    {
        $schema = new AttributeSchema(
            'name',
            'type',
            false,
            'Some description that is a little bit longer',
            'numeric',
            'example'
        );

        $this->assertSame(
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

    public function testItWillConvertTypeToOpenApi()
    {
        $schema = new AttributeSchema(
            'name',
            'int',
            false,
            'Some description that is a little bit longer',
            'numeric',
            'example'
        );

        $this->assertSame(
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
