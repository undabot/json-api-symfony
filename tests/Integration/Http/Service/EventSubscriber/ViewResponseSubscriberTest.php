<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Http\Service\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Undabot\JsonApi\Implementation\Encoding\AttributeCollectionToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\DocumentDataToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\DocumentToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\ErrorCollectionToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\ErrorToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\LinkCollectionToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\LinkToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\MetaToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\RelationshipCollectionToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\RelationshipToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\ResourceCollectionToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\ResourceIdentifierCollectionToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\ResourceIdentifierToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\ResourceToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Encoding\SourceToPhpArrayEncoder;
use Undabot\JsonApi\Implementation\Model\Meta\Meta;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Service\EventSubscriber\ViewResponseSubscriber;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
#[CoversClass('\Undabot\SymfonyJsonApi\Http\Service\EventSubscriber\ViewResponseSubscriber')]
#[Medium]
final class ViewResponseSubscriberTest extends TestCase
{
    private ViewResponseSubscriber $viewResponseSubscriber;

    protected function setUp(): void
    {
        $metaToPhpArrayEncoder = new MetaToPhpArrayEncoder();
        $linkToPhpArrayEncoder = new LinkToPhpArrayEncoder($metaToPhpArrayEncoder);
        $resourceIdentifierToPhpArrayEncoder = new ResourceIdentifierToPhpArrayEncoder($metaToPhpArrayEncoder);
        $linkCollectionToPhpArrayEncoder = new LinkCollectionToPhpArrayEncoder($linkToPhpArrayEncoder);
        $resourceToPhpArrayEncoder = new ResourceToPhpArrayEncoder(
            $metaToPhpArrayEncoder,
            new RelationshipCollectionToPhpArrayEncoder(
                new RelationshipToPhpArrayEncoder(
                    $metaToPhpArrayEncoder,
                    $linkCollectionToPhpArrayEncoder,
                    $resourceIdentifierToPhpArrayEncoder,
                ),
            ),
            $linkToPhpArrayEncoder,
            new AttributeCollectionToPhpArrayEncoder(),
        );
        $resourceCollectionToPhpArrayEncoder = new ResourceCollectionToPhpArrayEncoder($resourceToPhpArrayEncoder);
        $this->viewResponseSubscriber = new ViewResponseSubscriber(
            new DocumentToPhpArrayEncoder(
                new DocumentDataToPhpArrayEncoder(
                    $resourceToPhpArrayEncoder,
                    $resourceCollectionToPhpArrayEncoder,
                    $resourceIdentifierToPhpArrayEncoder,
                    new ResourceIdentifierCollectionToPhpArrayEncoder($resourceIdentifierToPhpArrayEncoder),
                ),
                new ErrorCollectionToPhpArrayEncoder(
                    new ErrorToPhpArrayEncoder(
                        $linkToPhpArrayEncoder,
                        new SourceToPhpArrayEncoder(),
                        $metaToPhpArrayEncoder
                    )
                ),
                $metaToPhpArrayEncoder,
                $linkCollectionToPhpArrayEncoder,
                $resourceCollectionToPhpArrayEncoder,
            ),
        );
    }

    public function testBuildViewWillSetCorrectResponseWithoutLinksInEventGivenValidResourceCollectionControllerWithoutPaginationResult(): void
    {
        $request = Request::create('http://localhost:8000/web/v1/posts');
        $event = new ViewEvent(
            new HttpKernel(new EventDispatcher(), new ControllerResolver()),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new ResourceCollectionResponse(
                new ResourceCollection([
                    new Resource(
                        '123',
                        'foo',
                        new AttributeCollection([new Attribute('bar', 'baz')])
                    ),
                ])
            ),
        );

        $this->viewResponseSubscriber->buildView($event);
        $responseString = $event->getResponse()->getContent();
        self::assertIsString($responseString);
        $responseContent = json_decode($responseString, true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayNotHasKey('links', $responseContent);
    }

    #[DataProvider('provideBuildViewWillSetCorrectResponseWithPaginationLinksInEventGivenValidResourceCollectionControllerWithPaginatedResultsCases')]
    public function testBuildViewWillSetCorrectResponseWithPaginationLinksInEventGivenValidResourceCollectionControllerWithPaginatedResults(
        Request $request,
        ?string $firstLink,
        ?string $prevLink,
        ?string $nextLink,
        ?string $lastLink
    ): void {
        $event = $this->getViewEvent($request);
        $this->viewResponseSubscriber->buildView($event);
        $responseString = $event->getResponse()->getContent();
        self::assertIsString($responseString);
        $responseContent = json_decode($responseString, true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('links', $responseContent);
        $links = $responseContent['links'];
        $this->assertLinkCanExistAndIsValidIfExists($links, 'first', $firstLink);
        $this->assertLinkCanExistAndIsValidIfExists($links, 'prev', $prevLink);
        $this->assertLinkCanExistAndIsValidIfExists($links, 'next', $nextLink);
        $this->assertLinkCanExistAndIsValidIfExists($links, 'last', $lastLink);
    }

    public static function provideBuildViewWillSetCorrectResponseWithPaginationLinksInEventGivenValidResourceCollectionControllerWithPaginatedResultsCases(): iterable
    {
        yield 'Page based pagination with 1st page retrieved' => [
            Request::create(
                'http://localhost:8000/web/v1/posts',
                Request::METHOD_GET,
                ['page' => ['size' => 3, 'number' => 1]]
            ),
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=1',
            null,
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=2',
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=4',
        ];

        yield 'Page based pagination with 2nd page retrieved' => [
            Request::create(
                'http://localhost:8000/web/v1/posts',
                Request::METHOD_GET,
                ['page' => ['size' => 3, 'number' => 2]]
            ),
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=1',
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=1',
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=3',
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=4',
        ];

        yield 'Page based pagination with 3rd page retrieved' => [
            Request::create(
                'http://localhost:8000/web/v1/posts',
                Request::METHOD_GET,
                ['page' => ['size' => 3, 'number' => 3]]
            ),
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=1',
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=2',
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=4',
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=4',
        ];

        yield 'Page based pagination with last page retrieved' => [
            Request::create(
                'http://localhost:8000/web/v1/posts',
                Request::METHOD_GET,
                ['page' => ['size' => 3, 'number' => 4]]
            ),
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=1',
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=3',
            null,
            'http://localhost:8000/web/v1/posts?page[size]=3&page[number]=4',
        ];

        yield 'Offset based pagination with 1st page retrieved' => [
            Request::create(
                'http://localhost:8000/web/v1/posts',
                Request::METHOD_GET,
                ['page' => ['offset' => 0, 'limit' => 3]]
            ),
            'http://localhost:8000/web/v1/posts?page[offset]=0&page[limit]=3',
            null,
            'http://localhost:8000/web/v1/posts?page[offset]=3&page[limit]=3',
            'http://localhost:8000/web/v1/posts?page[offset]=9&page[limit]=3',
        ];

        yield 'Offset based pagination with 2nd page retrieved' => [
            Request::create(
                'http://localhost:8000/web/v1/posts',
                Request::METHOD_GET,
                ['page' => ['offset' => 3, 'limit' => 3]]
            ),
            'http://localhost:8000/web/v1/posts?page[offset]=0&page[limit]=3',
            'http://localhost:8000/web/v1/posts?page[offset]=0&page[limit]=3',
            'http://localhost:8000/web/v1/posts?page[offset]=6&page[limit]=3',
            'http://localhost:8000/web/v1/posts?page[offset]=9&page[limit]=3',
        ];

        yield 'Offset based pagination with 3rd page retrieved' => [
            Request::create(
                'http://localhost:8000/web/v1/posts',
                Request::METHOD_GET,
                ['page' => ['offset' => 6, 'limit' => 3]]
            ),
            'http://localhost:8000/web/v1/posts?page[offset]=0&page[limit]=3',
            'http://localhost:8000/web/v1/posts?page[offset]=3&page[limit]=3',
            'http://localhost:8000/web/v1/posts?page[offset]=9&page[limit]=3',
            'http://localhost:8000/web/v1/posts?page[offset]=9&page[limit]=3',
        ];

        yield 'Offset based pagination with last page retrieved' => [
            Request::create(
                'http://localhost:8000/web/v1/posts',
                Request::METHOD_GET,
                ['page' => ['offset' => 9, 'limit' => 3]]
            ),
            'http://localhost:8000/web/v1/posts?page[offset]=0&page[limit]=3',
            'http://localhost:8000/web/v1/posts?page[offset]=6&page[limit]=3',
            null,
            'http://localhost:8000/web/v1/posts?page[offset]=9&page[limit]=3',
        ];
    }

    private function assertLinkCanExistAndIsValidIfExists(array $links, string $key, ?string $keyValue): void
    {
        if (null !== $keyValue) {
            self::assertArrayHasKey($key, $links);
            self::assertEquals($keyValue, $links[$key]);
        } else {
            self::assertArrayNotHasKey($key, $links);
        }
    }

    private function getViewEvent(Request $request): ViewEvent
    {
        return new ViewEvent(
            new HttpKernel(new EventDispatcher(), new ControllerResolver()),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new ResourceCollectionResponse(
                new ResourceCollection([
                    new Resource(
                        '1',
                        'foo',
                        new AttributeCollection([new Attribute('bar', 'baz')])
                    ),
                    new Resource(
                        '2',
                        'foo',
                        new AttributeCollection([new Attribute('baz', 'bat')])
                    ),
                    new Resource(
                        '3',
                        'foo',
                        new AttributeCollection([new Attribute('baz', 'bat')])
                    ),
                ]),
                null,
                new Meta(['total' => 10])
            ),
        );
    }
}
