<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Http\Service\ArgumentResolver;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Undabot\JsonApi\Definition\Exception\Request\ClientGeneratedIdIsNotAllowedException;
use Undabot\JsonApi\Definition\Model\Request\CreateResourceRequestInterface;
use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Http\Model\Request\CreateResourceRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Service\ArgumentResolver\JsonApiRequestOptions;
use Undabot\SymfonyJsonApi\Http\Service\ArgumentResolver\JsonApiRequestValueResolver;
use Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory;

/**
 * @internal
 */
#[CoversClass(JsonApiRequestValueResolver::class)]
#[Small]
#[AllowMockObjectsWithoutExpectations]
final class JsonApiRequestValueResolverTest extends TestCase
{
    private MockObject&RequestFactory $requestFactoryMock;
    private JsonApiRequestValueResolver $resolver;

    protected function setUp(): void
    {
        $this->requestFactoryMock = $this->createMock(RequestFactory::class);

        $this->resolver = new JsonApiRequestValueResolver($this->requestFactoryMock);
    }

    #[DataProvider('provideResolveThrowsForCreateResourceRequestsWithClientProvidedResourceIdsCases')]
    public function testResolveThrowsForCreateResourceRequestsWithClientProvidedResourceIds(array $attributes): void
    {
        $request = self::createStub(Request::class);
        $argument = new ArgumentMetadata('createRequest', CreateResourceRequestInterface::class, false, false, null, false, $attributes);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('requestResourceHasClientSideGeneratedId')
            ->willReturn(true);

        $this->expectException(ClientGeneratedIdIsNotAllowedException::class);

        $this->resolver->resolve($request, $argument);
    }

    public static function provideResolveThrowsForCreateResourceRequestsWithClientProvidedResourceIdsCases(): iterable
    {
        return [
            'does not support client generated ID' => [
                [new JsonApiRequestOptions(clientGeneratedIds: false)],
            ],
            'default empty configuration' => [
                [],
            ],
        ];
    }

    public function testResolveAllowsClientProvidedResourceIdsWhenEnabled(): void
    {
        $request = self::createStub(Request::class);
        $argument = new ArgumentMetadata(
            'createRequest',
            CreateResourceRequestInterface::class,
            false,
            false,
            null,
            false,
            [new JsonApiRequestOptions(clientGeneratedIds: true)],
        );

        $createRequest = self::createStub(CreateResourceRequest::class);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('createResourceRequest')
            ->with($request, null)
            ->willReturn($createRequest);

        self::assertSame([$createRequest], [...$this->resolver->resolve($request, $argument)]);
    }

    public function testResolveResolvesResourceCollectionRequest(): void
    {
        $request = self::createStub(Request::class);
        $argument = new ArgumentMetadata('collectionRequest', GetResourceCollectionRequestInterface::class, false, false, null);

        $collectionRequest = self::createStub(GetResourceCollectionRequest::class);

        $this->requestFactoryMock
            ->expects(self::once())
            ->method('getResourceCollectionRequest')
            ->with($request)
            ->willReturn($collectionRequest);

        self::assertSame([$collectionRequest], [...$this->resolver->resolve($request, $argument)]);
    }

    public function testResolveIgnoresUnsupportedArgumentTypes(): void
    {
        $request = self::createStub(Request::class);
        $argument = new ArgumentMetadata('foo', \stdClass::class, false, false, null);

        self::assertSame([], [...$this->resolver->resolve($request, $argument)]);
    }
}
