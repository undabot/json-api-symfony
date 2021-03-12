<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\ResourceReadSchema;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\AttributeSchemaFactory;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\RelationshipSchemaFactory;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\ResourceSchemaFactory;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as JsonApi;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\ResourceSchemaFactory
 *
 * @small
 */
final class ResourceReadSchemaTest extends TestCase
{
    private ResourceSchemaFactory $resourceSchemaFactory;

    protected function setUp(): void
    {
        $metadataFactory = new ResourceMetadataFactory(new AnnotationReader());
        $attributeSchemaFactory = new AttributeSchemaFactory();
        $relationshipSchemaFactory = new RelationshipSchemaFactory();
        $this->resourceSchemaFactory = new ResourceSchemaFactory(
            $metadataFactory,
            $attributeSchemaFactory,
            $relationshipSchemaFactory
        );
    }

    public function testResourceAttributesAreCorrectlyConverted(): void
    {
        /** @ResourceType(type="testResource") */
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\Attribute(name="name", description="The name", format="NAME", example="My Name")
             */
            public string $nameProperty;

            /**
             * @JsonApi\Attribute(name="nullableName", description="The name", format="NAME", example="My Name", nullable=true)
             */
            public ?string $nullableNameProperty;

            /**
             * @JsonApi\Attribute
             * @Assert\Type(type="integer")
             */
            public int $integerProperty;

            /**
             * @JsonApi\Attribute(nullable=true)
             * @Assert\Type(type="integer")
             */
            public ?int $nullableIntegerProperty;

            /**
             * @JsonApi\Attribute
             * @Assert\Type(type="boolean")
             */
            public bool $booleanProperty1;

            /**
             * @JsonApi\Attribute
             * @Assert\Type(type="bool")
             */
            public bool $booleanProperty2;

            /**
             * @JsonApi\Attribute
             * @Assert\Type(type="float")
             */
            public float $floatProperty;
        };
        $className = \get_class($resource);

        $resourceReadSchema = $this->resourceSchemaFactory->readSchema($className);

        $resourceSchema = $resourceReadSchema->toOpenApi();
        static::assertIsArray($resourceSchema);
        static::assertSame($resourceSchema['type'], 'object');
        static::assertSame(
            ['id', 'type', 'attributes'],
            $resourceSchema['required']
        );

        static::assertSame(
            ['type' => 'string'],
            $resourceSchema['properties']['id']
        );

        static::assertSame(
            [
                'title' => 'name',
                'type' => 'string',
                'nullable' => false,
                'description' => 'The name',
                'example' => 'My Name',
                'format' => 'NAME',
            ],
            $resourceSchema['properties']['attributes']['properties']['name']
        );

        static::assertSame(
            [
                'title' => 'nullableName',
                'type' => 'string',
                'nullable' => true,
                'description' => 'The name',
                'example' => 'My Name',
                'format' => 'NAME',
            ],
            $resourceSchema['properties']['attributes']['properties']['nullableName']
        );

        static::assertSame(
            [
                'title' => 'integerProperty',
                'type' => 'integer',
                'nullable' => false,
            ],
            $resourceSchema['properties']['attributes']['properties']['integerProperty']
        );

        static::assertSame(
            [
                'title' => 'nullableIntegerProperty',
                'type' => 'integer',
                'nullable' => true,
            ],
            $resourceSchema['properties']['attributes']['properties']['nullableIntegerProperty']
        );

        static::assertSame(
            [
                'title' => 'booleanProperty1',
                'type' => 'boolean',
                'nullable' => false,
            ],
            $resourceSchema['properties']['attributes']['properties']['booleanProperty1']
        );

        static::assertSame(
            [
                'title' => 'booleanProperty2',
                'type' => 'boolean',
                'nullable' => false,
            ],
            $resourceSchema['properties']['attributes']['properties']['booleanProperty2']
        );

        static::assertSame(
            [
                'title' => 'floatProperty',
                'type' => 'number',
                'nullable' => false,
            ],
            $resourceSchema['properties']['attributes']['properties']['floatProperty']
        );
    }
}
