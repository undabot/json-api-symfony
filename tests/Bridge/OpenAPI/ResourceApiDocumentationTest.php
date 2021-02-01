<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI;

use Doctrine\Common\Annotations\AnnotationReader;
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
 * @covers \Undabot\SymfonyJsonApi\Bridge\OpenApi\Service\ResourceSchemaFactory
 *
 * @small
 */
final class ResourceApiDocumentationTest extends TestCase
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
            ->new('/articles', Article::class)
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

        $documentation = $api->toOpenApi();

        static::assertIsArray($documentation);
        static::assertArrayHasKey('openapi', $documentation);
        static::assertSame('3.0.0', $documentation['openapi']);
        static::assertArrayHasKey('info', $documentation);
        static::assertArrayHasKey('description', $documentation['info']);
        static::assertArrayHasKey('version', $documentation['info']);
        static::assertArrayHasKey('title', $documentation['info']);
        static::assertSame('Example API documentation for JSON:API', $documentation['info']['description']);
        static::assertSame('1.0.0', $documentation['info']['version']);
        static::assertSame('Test API', $documentation['info']['title']);
        static::assertArrayHasKey('paths', $documentation);
        static::assertArrayHasKey('/articles', $documentation['paths']);
        static::assertArrayHasKey('get', $documentation['paths']['/articles']);
        static::assertArrayHasKey('summary', $documentation['paths']['/articles']['get']);
        static::assertSame('List article', $documentation['paths']['/articles']['get']['summary']);
        static::assertArrayHasKey('operationId', $documentation['paths']['/articles']['get']);
        static::assertSame('listArticleCollection', $documentation['paths']['/articles']['get']['operationId']);
        static::assertArrayHasKey('description', $documentation['paths']['/articles']['get']);
        static::assertSame('List collection of article', $documentation['paths']['/articles']['get']['description']);
        static::assertArrayHasKey('parameters', $documentation['paths']['/articles']['get']);
    }
}
