<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Http\Service\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Definition\Encoding\PhpArrayToResourceEncoderInterface;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceRequest;
use Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory;
use Undabot\SymfonyJsonApi\Http\Service\Validation\RequestValidator;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory
 *
 * @small
 */
final class RequestFactoryTest extends TestCase
{
    /** @var MockObject */
    private $resourceEncoder;

    /** @var MockObject */
    private $requestValidator;

    /** @var RequestFactory */
    private $requestFactory;

    protected function setUp(): void
    {
        $this->resourceEncoder = $this->createMock(PhpArrayToResourceEncoderInterface::class);
        $this->requestValidator = $this->createMock(RequestValidator::class);
        $this->requestFactory = new RequestFactory($this->resourceEncoder, $this->requestValidator);
    }

    /**
     * @dataProvider requestParamsProvider
     */
    public function testGetResourceRequestWillReturnValidGetResourceRequestGivenValidRequest(array $queryParams, ?array $include, ?array $fields): void
    {
        $id = '123';

        $resourceRequest = new GetResourceRequest($id, $include, $fields);

        $request = $this->createMock(Request::class);
        $query = new ParameterBag($queryParams);
        $request->query = $query;
        $this->requestValidator->expects(static::once())->method('assertValidRequest');

        $getResourceRequest = $this->requestFactory->getResourceRequest($request, $id);

        static::assertEquals($resourceRequest, $getResourceRequest);
    }

    public function requestParamsProvider(): \Generator
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
}
