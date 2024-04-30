<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Http\Service\Factory;

use Assert\AssertionFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Undabot\JsonApi\Definition\Encoding\PhpArrayToResourceEncoderInterface;
use Undabot\JsonApi\Definition\Model\Request\Pagination\PaginationInterface;
use Undabot\JsonApi\Implementation\Model\Request\Filter\Filter;
use Undabot\JsonApi\Implementation\Model\Request\Filter\FilterSet;
use Undabot\JsonApi\Implementation\Model\Request\Pagination\PageBasedPagination;
use Undabot\JsonApi\Implementation\Model\Request\Sort\Sort;
use Undabot\JsonApi\Implementation\Model\Request\Sort\SortSet;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceRequest;
use Undabot\SymfonyJsonApi\Http\Model\Request\UpdateResourceRequest;
use Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory;
use Undabot\SymfonyJsonApi\Http\Service\Validation\RequestValidator;

/**
 * @internal
 *
 * @covers \Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory
 *
 * @medium
 */
final class RequestFactoryTest extends TestCase
{
    private MockObject $resourceEncoderMock;
    private MockObject $requestValidatorMock;
    private MockObject $requestStackMock;
    private RequestFactory $requestFactory;

    protected function setUp(): void
    {
        $this->resourceEncoderMock = $this->createMock(PhpArrayToResourceEncoderInterface::class);
        $this->requestValidatorMock = $this->createMock(RequestValidator::class);
        $this->requestStackMock = $this->createMock(RequestStack::class);
        $this->requestFactory = new RequestFactory(
            $this->resourceEncoderMock,
            $this->requestValidatorMock,
            $this->requestStackMock,
        );
    }

    /**
     * @dataProvider provideGetResourceRequestWillReturnValidGetResourceRequestGivenValidRequestCases
     */
    public function testGetResourceRequestWillReturnValidGetResourceRequestGivenValidRequest(
        array $queryParams,
        ?array $include,
        ?array $fields
    ): void {
        $id = '123';

        $resourceRequest = new GetResourceRequest($id, $include, $fields);

        $request = $this->createMock(Request::class);
        $query = new InputBag($queryParams);
        $request->query = $query;
        $attributes = $this->createMock(ParameterBag::class);
        $attributes->expects(self::once())->method('get')->willReturn($id);
        $request->attributes = $attributes;
        $this->requestValidatorMock->expects(self::once())->method('assertValidRequest');
        $this->requestStackMock->expects(self::once())->method('getMainRequest')->willReturn($request);

        $getResourceRequest = $this->requestFactory->getResourceRequest();

        self::assertEquals($resourceRequest, $getResourceRequest);
    }

    public static function provideGetResourceRequestWillReturnValidGetResourceRequestGivenValidRequestCases(): iterable
    {
        yield 'No include and no fields in request get params' => [
            [],
            null,
            null,
        ];

        yield 'No include and fields present in request get params' => [
            ['fields' => ['foo', 'bar']],
            null,
            ['foo', 'bar'],
        ];

        yield 'Include string provided with multiple values and no fields in request get params' => [
            ['include' => 'foo,bar,baz'],
            ['foo', 'bar', 'baz'],
            null,
        ];

        yield 'Include string provided with single value and no fields in request get params' => [
            ['include' => 'foo'],
            ['foo'],
            null,
        ];

        yield 'Both include and fields provided in request get params' => [
            ['include' => 'foo,bar,baz', 'fields' => ['foo', 'bar']],
            ['foo', 'bar', 'baz'],
            ['foo', 'bar'],
        ];
    }

    /**
     * @dataProvider provideGetResourceCollectionRequestWillReturnValidGetResourceCollectionRequestGivenValidRequestCases
     */
    public function testGetResourceCollectionRequestWillReturnValidGetResourceCollectionRequestGivenValidRequest(
        array $queryParams,
        ?PaginationInterface $pagination,
        ?FilterSet $filterSet,
        ?SortSet $sortSet,
        ?array $include,
        ?array $fields
    ): void {
        $resourceCollectionRequest = new GetResourceCollectionRequest($pagination, $filterSet, $sortSet, $include, $fields);

        $request = $this->createMock(Request::class);
        $query = new InputBag($queryParams);
        $request->query = $query;
        $this->requestValidatorMock->expects(self::once())->method('assertValidRequest');
        $this->requestStackMock->expects(self::once())->method('getMainRequest')->willReturn($request);

        $getResourceCollectionRequest = $this->requestFactory->getResourceCollectionRequest();

        self::assertEquals($resourceCollectionRequest, $getResourceCollectionRequest);
    }

    public static function provideGetResourceCollectionRequestWillReturnValidGetResourceCollectionRequestGivenValidRequestCases(): iterable
    {
        yield 'No params provided' => [
            [],
            null,
            null,
            null,
            null,
            null,
        ];

        yield 'Pagination params provided' => [
            ['page' => ['size' => 10, 'number' => 2]],
            new PageBasedPagination(2, 10),
            null,
            null,
            null,
            null,
        ];

        yield 'Filter params provided' => [
            ['filter' => ['foo' => 'bar']],
            null,
            new FilterSet([new Filter('foo', 'bar')]),
            null,
            null,
            null,
        ];

        yield 'Sort params provided' => [
            ['sort' => '-foo,bar'],
            null,
            null,
            new SortSet([new Sort('foo', 'DESC'), new Sort('bar', 'ASC')]),
            null,
            null,
        ];

        yield 'Include params provided' => [
            ['include' => 'foo,bar,baz'],
            null,
            null,
            null,
            ['foo', 'bar', 'baz'],
            null,
        ];

        yield 'Fields params provided' => [
            ['fields' => ['foo', 'bar', 'baz']],
            null,
            null,
            null,
            null,
            ['foo', 'bar', 'baz'],
        ];

        yield 'All params provided' => [
            [
                'page' => ['size' => 10, 'number' => 2],
                'filter' => ['foo' => 'bar'],
                'sort' => '-foo,bar',
                'include' => 'foo,bar,baz',
                'fields' => ['foo', 'bar', 'baz'],
            ],
            new PageBasedPagination(2, 10),
            new FilterSet([new Filter('foo', 'bar')]),
            new SortSet([new Sort('foo', 'DESC'), new Sort('bar', 'ASC')]),
            ['foo', 'bar', 'baz'],
            ['foo', 'bar', 'baz'],
        ];
    }

    public function testUpdateResourceRequestWillReturnValidUpdateResourceRequestObjectGivenValidRequestPrimaryData(): void
    {
        $id = '123';

        $request = $this->createMock(Request::class);
        $attributes = $this->createMock(ParameterBag::class);
        $attributes->expects(self::once())->method('get')->willReturn($id);
        $request->attributes = $attributes;
        $request->expects(self::once())->method('getContent')->willReturn('{"data": {"foo": "bar"}}');

        $resource = new Resource($id, 'type', new AttributeCollection([new Attribute('foo', 'bar')]), null, null, null);
        $this->requestValidatorMock->expects(self::once())->method('assertValidRequest');
        $this->requestValidatorMock->expects(self::once())->method('assertValidUpdateRequestData');
        $this->resourceEncoderMock
            ->expects(self::once())
            ->method('decode')
            ->willReturn($resource);
        $this->requestStackMock->expects(self::exactly(2))->method('getMainRequest')->willReturn($request);

        $updateResourceRequest = $this->requestFactory->updateResourceRequest();

        self::assertInstanceOf(UpdateResourceRequest::class, $updateResourceRequest);
        self::assertEquals($resource, $updateResourceRequest->getResource());
    }

    /**
     * @dataProvider invalidRequestPrimaryDataProvider
     */
    public function testUpdateResourceRequestWillThrowExceptionGivenInvalidRequestPrimaryData(
        ?string $content,
        string $exceptionMessage
    ): void {
        $id = '123';

        $request = $this->createMock(Request::class);
        $attributes = $this->createMock(ParameterBag::class);
        $attributes->expects(self::once())->method('get')->willReturn($id);
        $request->attributes = $attributes;
        $request->expects(self::once())->method('getContent')->willReturn($content);

        $this->requestValidatorMock->expects(self::once())->method('assertValidRequest');
        $this->requestValidatorMock->expects(self::never())->method('assertValidUpdateRequestData');
        $this->resourceEncoderMock
            ->expects(self::never())
            ->method('decode');
        $this->requestStackMock->expects(self::exactly(2))->method('getMainRequest')->willReturn($request);

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestFactory->updateResourceRequest();
    }

    /**
     * @dataProvider invalidRequestPrimaryDataProvider
     */
    public function testRequestResourceHasClientSideGeneratedIdWillThrowExceptionGivenInvalidRequestPrimaryData(
        ?string $content,
        string $exceptionMessage
    ): void {
        $request = $this->createMock(Request::class);
        $request->expects(self::once())->method('getContent')->willReturn($content);
        $this->requestStackMock->expects(self::once())->method('getMainRequest')->willReturn($request);

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->requestFactory->requestResourceHasClientSideGeneratedId();
    }

    public static function invalidRequestPrimaryDataProvider(): iterable
    {
        yield 'Invalid json string' => [
            '{"foo": "bar"]}',
            'Request data must be valid JSON',
        ];

        yield 'Null data given' => [
            null,
            'Request data must be valid JSON',
        ];

        yield 'Not array given' => [
            '2',
            'Request data must be parsable to a valid array',
        ];

        yield 'Json string does not have data key' => [
            '{"foo": "bar"}',
            'The request MUST include a single resource object as primary data',
        ];
    }

    /**
     * @dataProvider provideRequestResourceHasClientSideGeneratedIdWillReturnCorrectIdPresenceGivenValidRequestPrimaryDataCases
     */
    public function testRequestResourceHasClientSideGeneratedIdWillReturnCorrectIdPresenceGivenValidRequestPrimaryData(
        string $content,
        bool $hadId
    ): void {
        $request = $this->createMock(Request::class);
        $request->expects(self::once())->method('getContent')->willReturn($content);
        $this->requestStackMock->expects(self::once())->method('getMainRequest')->willReturn($request);

        self::assertEquals($hadId, $this->requestFactory->requestResourceHasClientSideGeneratedId());
    }

    public static function provideRequestResourceHasClientSideGeneratedIdWillReturnCorrectIdPresenceGivenValidRequestPrimaryDataCases(): iterable
    {
        yield 'Create request does not have client generated id' => [
            '{"data": {"id": "123", "foo": "bar"}}',
            true,
        ];

        yield 'Create request have client generated id' => [
            '{"data": {"foo": "bar"}}',
            false,
        ];

        yield 'Create request have lid' => [
            '{"data": {"lid": "123", "foo": "bar"}}',
            false,
        ];
    }

    public function testCreateResourceRequestWillReturnResourceWithLidAsRequestIdentifierGivenRequestHasLidInBody(): void
    {
        $lid = '123';
        $request = $this->createMock(Request::class);
        $attributes = $this->createMock(ParameterBag::class);
        $attributes->expects(self::once())->method('get')->willReturn($lid);
        $request->attributes = $attributes;
        $request->expects(self::once())->method('getContent')->willReturn('{"data": {"lid": "123", "foo": "bar"}}');
        $resource = new Resource($lid, 'type', new AttributeCollection([new Attribute('foo', 'bar')]));
        $this->resourceEncoderMock
            ->expects(self::once())
            ->method('decode')
            ->willReturn($resource);
        $this->requestStackMock->method('getMainRequest')->willReturn($request);

        self::assertEquals($lid, $this->requestFactory->createResourceRequest()->getResource()->getId());
    }
}
