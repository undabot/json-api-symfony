<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\ResourceReadSchema;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
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
final class ResourceReadSchemaRelationshipsTest extends TestCase
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

    public function testToOneRelationshipIsCorrectlyConverted(): void
    {
        /** @ResourceType(type="testResource") */
        $resource = new class() implements ApiModel {
            /**
             * @JsonApi\ToOne(type="targetResource", description="Relationship description", name="target")
             */
            public $targetId;
        };
        $className = \get_class($resource);
        $resourceReadSchema = $this->resourceSchemaFactory->readSchema($className);

        $resourceSchema = $resourceReadSchema->toOpenApi();
        static::assertIsArray($resourceSchema);
        //var_dump($resourceSchema);

        //exit;
    }
}
