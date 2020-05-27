<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\Api;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Filter\Filter;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\Server;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\AttributeSchemaFactory;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\RelationshipSchemaFactory;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\ResourceApiEndpointsFactory;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\ResourceSchemaFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Model\Article;
use Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Model\Category;
use Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Model\Tag;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class ResourceApiDocumentationTest extends TestCase
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

    public function testDocumentationCanBeGenerated(): void
    {
        $api = new Api(
            'Test API',
            '1.0.0',
            'Example API documentation for JSON:API'
        );

        $api->addServer(new Server('http://jsonapi.undabot.com'));

        $endpointFactory = new ResourceApiEndpointsFactory($this->resourceSchemaFactory);

        $endpointFactory
            ->new()
            ->atPath('/articles')
            ->forResource(Article::class)
            ->withGetCollection()
            ->withCollectionIncludes(
                [
                    'category' => Category::class,
                    'tag' => Tag::class,
                ]
            )
            ->withCollectionFilters(
                [
                    'category' => Filter::integer('Category ID', true, 'ID of the category', 1),
                    'tag' => Filter::string('Comma separated Tag IDs', false, 'ID of the category', '1,2,3'),
                ]
            )
            ->withPageBasedPagination()
            ->withCreate()
            ->withUpdate()
            ->withGetSingle()
            ->withSingleIncludes(
                [
                    'category' => Category::class,
                    'tag' => Tag::class,
                ]
            )
            ->addToApi($api);

        echo json_encode($api->toOpenApi(), JSON_PRETTY_PRINT);
        exit;
    }
}
