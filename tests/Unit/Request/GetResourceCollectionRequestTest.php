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
 *
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
        $this->parameterBagMock->method('all')->willReturn([]);

        $getResourceCollectionRequest = $this->requestFactory->getResourceCollectionRequest($this->requestMock);
        self::assertInstanceOf(GetResourceCollectionRequest::class, $getResourceCollectionRequest);

        self::assertNull($getResourceCollectionRequest->getPagination());
        self::assertNull($getResourceCollectionRequest->getIncludes());
        self::assertNull($getResourceCollectionRequest->getFilterSet());
        self::assertNull($getResourceCollectionRequest->getSortSet());
        self::assertNull($getResourceCollectionRequest->getSparseFieldset());
    }

    public function testItWithAllValidParametersCanBeConstructed(): void
    {
        $queryParamsMap = [
            'page' => ['number' => 3, 'size' => 10],
            'filter' => ['priceMin' => 3, 'priceMax' => 10.5, 'name' => 'John'],
            'sort' => 'name,-price,author.name',
            'include' => 'category,history,purchases',
            'fields' => ['author' => 'name,price,rating', 'book' => 'title,publisher'],
        ];

        $this->parameterBagMock->method('all')->willReturn($queryParamsMap);

        $this->requestMock->query = $this->parameterBagMock;

        $getResourceCollectionRequest = $this->requestFactory->getResourceCollectionRequest($this->requestMock);
        self::assertInstanceOf(GetResourceCollectionRequest::class, $getResourceCollectionRequest);

        self::assertSame(10, $getResourceCollectionRequest->getPagination()->getSize());
        self::assertSame((3 - 1) * 10, $getResourceCollectionRequest->getPagination()->getOffset());

        $filters = $getResourceCollectionRequest->getFilterSet();
        self::assertSame(3, $filters->getFilter('priceMin')->getValue());
        self::assertSame(10.5, $filters->getFilter('priceMax')->getValue());
        self::assertSame('John', $filters->getFilter('name')->getValue());

        $sorts = iterator_to_array($getResourceCollectionRequest->getSortSet());

        self::assertSame($sorts[0]->getAttribute(), 'name');
        self::assertTrue($sorts[0]->isAsc());

        self::assertSame($sorts[1]->getAttribute(), 'price');
        self::assertTrue($sorts[1]->isDesc());

        self::assertSame($sorts[2]->getAttribute(), 'author.name');
        self::assertTrue($sorts[2]->isAsc());

        $includes = $getResourceCollectionRequest->getIncludes();
        self::assertSame(['category', 'history', 'purchases'], $includes);

        $fields = $getResourceCollectionRequest->getSparseFieldset();
        self::assertSame(['author' => 'name,price,rating', 'book' => 'title,publisher'], $fields);
    }
}
