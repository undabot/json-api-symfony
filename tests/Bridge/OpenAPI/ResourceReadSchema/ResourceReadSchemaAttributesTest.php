<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\ResourceReadSchema;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
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
 * @coversNothing
 *
 * @small
 */
final class ResourceReadSchemaAttributesTest extends TestCase
{
    /** @var ResourceSchemaFactory */
    private $resourceSchemaFactory;

    protected function setUp(): void
    {
        AnnotationRegistry::registerLoader('class_exists');
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
            public $nameProperty;

            /**
             * @JsonApi\Attribute
             * @Assert\Type(type="integer")
             */
            public $integerProperty;

            /**
             * @JsonApi\Attribute
             * @Assert\Type(type="boolean")
             */
            public $booleanProperty1;

            /**
             * @JsonApi\Attribute
             * @Assert\Type(type="bool")
             */
            public $booleanProperty2;

            /**
             * @JsonApi\Attribute
             * @Assert\Type(type="float")
             */
            public $floatProperty;
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
                'nullable' => true,
                'description' => 'The name',
                'example' => 'My Name',
                'format' => 'NAME',
            ],
            $resourceSchema['properties']['attributes']['properties']['name']
        );

        static::assertSame(
            [
                'title' => 'integerProperty',
                'type' => 'integer',
                'nullable' => true,
            ],
            $resourceSchema['properties']['attributes']['properties']['integerProperty']
        );

        static::assertSame(
            [
                'title' => 'booleanProperty1',
                'type' => 'boolean',
                'nullable' => true,
            ],
            $resourceSchema['properties']['attributes']['properties']['booleanProperty1']
        );

        static::assertSame(
            [
                'title' => 'booleanProperty2',
                'type' => 'boolean',
                'nullable' => true,
            ],
            $resourceSchema['properties']['attributes']['properties']['booleanProperty2']
        );

        static::assertSame(
            [
                'title' => 'floatProperty',
                'type' => 'number',
                'nullable' => true,
            ],
            $resourceSchema['properties']['attributes']['properties']['floatProperty']
        );
    }
}
