<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Request;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Definition\Encoding\PhpArrayToResourceEncoderInterface;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory;
use Undabot\SymfonyJsonApi\Http\Service\Validation\RequestValidator;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class GetResourceCollectionRequestTest extends TestCase
{
    private RequestFactory $requestFactory;
    private MockObject $requestMock;
    private MockObject $parameterBagMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Request::class);
        $this->parameterBagMock = $this->createMock(ParameterBag::class);
        $this->requestMock->query = $this->parameterBagMock;

        $resourceEncoder = $this->createMock(PhpArrayToResourceEncoderInterface::class);
        $requestValidatorMock = $this->createMock(RequestValidator::class);
        $this->requestFactory = new RequestFactory(
            $resourceEncoder,
            $requestValidatorMock
        );
    }

    public function testItWithoutAnyParametersCanBeConstructed(): void
    {
//        $parameterBagMock = $this->createMock(ParameterBag::class);
//        $requestMock = $this->createMock(Request::class);
        $this->parameterBagMock->method('get')->willReturn(null);

        $getResourceCollectionRequest = $this->requestFactory->getResourceCollectionRequest($this->requestMock);
        static::assertInstanceOf(GetResourceCollectionRequest::class, $getResourceCollectionRequest);

        static::assertNull($getResourceCollectionRequest->getPagination());
        static::assertNull($getResourceCollectionRequest->getIncludes());
        static::assertNull($getResourceCollectionRequest->getFilterSet());
        static::assertNull($getResourceCollectionRequest->getSortSet());
        static::assertNull($getResourceCollectionRequest->getSparseFieldset());
    }

    public function testItWithAllValidParametersCanBeConstructed(): void
    {
        $queryParamsMap = [
            ['page', null, ['number' => 3, 'size' => 10]],
            ['filter', null, ['priceMin' => 3, 'priceMax' => 10.5, 'name' => 'John']],
            ['sort', null, 'name,-price,author.name'],
            ['include', null, 'category,history,purchases'],
            ['fields', null, ['author' => 'name,price,rating', 'book' => 'title,publisher']],
        ];

        $this->parameterBagMock->method('get')->willReturnMap($queryParamsMap);

        $this->parameterBagMock->method('has')->willReturnCallback(static function ($param) use ($queryParamsMap) {
            $filtered = array_filter($queryParamsMap, static function ($item) use ($param) {
                return ($item[0] ?? null) === $param;
            });

            return 1 === \count($filtered);
        });
        $this->requestMock->query = $this->parameterBagMock;

        $getResourceCollectionRequest = $this->requestFactory->getResourceCollectionRequest($this->requestMock);
        static::assertInstanceOf(GetResourceCollectionRequest::class, $getResourceCollectionRequest);

        static::assertSame(10, $getResourceCollectionRequest->getPagination()->getSize());
        static::assertSame((3 - 1) * 10, $getResourceCollectionRequest->getPagination()->getOffset());

        $filters = $getResourceCollectionRequest->getFilterSet();
        static::assertSame(3, $filters->getFilter('priceMin')->getValue());
        static::assertSame(10.5, $filters->getFilter('priceMax')->getValue());
        static::assertSame('John', $filters->getFilter('name')->getValue());

        $sorts = iterator_to_array($getResourceCollectionRequest->getSortSet());

        static::assertSame($sorts[0]->getAttribute(), 'name');
        static::assertTrue($sorts[0]->isAsc());

        static::assertSame($sorts[1]->getAttribute(), 'price');
        static::assertTrue($sorts[1]->isDesc());

        static::assertSame($sorts[2]->getAttribute(), 'author.name');
        static::assertTrue($sorts[2]->isAsc());

        $includes = $getResourceCollectionRequest->getIncludes();
        static::assertSame(['category', 'history', 'purchases'], $includes);

        $fields = $getResourceCollectionRequest->getSparseFieldset();
        static::assertSame(['author' => 'name,price,rating', 'book' => 'title,publisher'], $fields);
    }
}
