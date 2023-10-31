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
 *
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

        self::assertIsArray($documentation);
        self::assertArrayHasKey('openapi', $documentation);
        self::assertSame('3.0.0', $documentation['openapi']);
        self::assertArrayHasKey('info', $documentation);
        self::assertArrayHasKey('description', $documentation['info']);
        self::assertArrayHasKey('version', $documentation['info']);
        self::assertArrayHasKey('title', $documentation['info']);
        self::assertSame('Example API documentation for JSON:API', $documentation['info']['description']);
        self::assertSame('1.0.0', $documentation['info']['version']);
        self::assertSame('Test API', $documentation['info']['title']);
        self::assertArrayHasKey('paths', $documentation);
        self::assertArrayHasKey('/articles', $documentation['paths']);
        self::assertArrayHasKey('get', $documentation['paths']['/articles']);
        self::assertArrayHasKey('summary', $documentation['paths']['/articles']['get']);
        self::assertSame('List article', $documentation['paths']['/articles']['get']['summary']);
        self::assertArrayHasKey('operationId', $documentation['paths']['/articles']['get']);
        self::assertSame('listArticleCollection', $documentation['paths']['/articles']['get']['operationId']);
        self::assertArrayHasKey('description', $documentation['paths']['/articles']['get']);
        self::assertSame('List collection of article', $documentation['paths']['/articles']['get']['description']);
        self::assertArrayHasKey('parameters', $documentation['paths']['/articles']['get']);
    }
}
