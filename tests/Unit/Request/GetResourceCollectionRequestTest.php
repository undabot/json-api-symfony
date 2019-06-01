<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;

class GetResourceCollectionRequestTest extends TestCase
{
    public function testRequestWithoutAnyParametersCanBeConstructed()
    {
        $parameterBagMock = $this->createMock(ParameterBag::class);
        $requestMock = $this->createMock(Request::class);
        $requestMock->query = $parameterBagMock;
        $parameterBagMock->method('get')->willReturn(null);

        $getResourceCollectionRequest = GetResourceCollectionRequest::createFromRequest($requestMock);
        $this->assertInstanceOf(GetResourceCollectionRequest::class, $getResourceCollectionRequest);

        $this->assertNull($getResourceCollectionRequest->getPagination());
        $this->assertNull($getResourceCollectionRequest->getInclude());
        $this->assertNull($getResourceCollectionRequest->getFilterSet());
        $this->assertNull($getResourceCollectionRequest->getSortSet());
        $this->assertNull($getResourceCollectionRequest->getSparseFieldset());
    }

    public function testRequestWithAllValidParametersCanBeConstructed()
    {
        $parameterBagMock = $this->createMock(ParameterBag::class);
        $requestMock = $this->createMock(Request::class);

        $map = [
            ['page', null, ['number' => 3, 'size' => 10]],
            ['filter', null, ['priceMin' => 3, 'priceMax' => 10.5, 'name' => 'John']],
            ['sort', null, 'name,-price,author.name'],
            ['include', null, 'category,history,purchases'],
            ['fields', null, ['author' => 'name,price,rating', 'book' => 'title,publisher']],
        ];

        $parameterBagMock->method('get')
            ->will($this->returnValueMap($map));

        $requestMock->query = $parameterBagMock;

        $getResourceCollectionRequest = GetResourceCollectionRequest::createFromRequest($requestMock);
        $this->assertInstanceOf(GetResourceCollectionRequest::class, $getResourceCollectionRequest);

        $this->assertSame(10, $getResourceCollectionRequest->getPagination()->getSize());
        $this->assertSame((3 - 1) * 10, $getResourceCollectionRequest->getPagination()->getOffset());

        $filters = $getResourceCollectionRequest->getFilterSet();
        $this->assertSame(3, $filters->getFilter('priceMin')->getValue());
        $this->assertSame(10.5, $filters->getFilter('priceMax')->getValue());
        $this->assertSame('John', $filters->getFilter('name')->getValue());

        $sorts = iterator_to_array($getResourceCollectionRequest->getSortSet());

        $this->assertSame($sorts[0]->getAttribute(), 'name');
        $this->assertTrue($sorts[0]->isAsc());

        $this->assertSame($sorts[1]->getAttribute(), 'price');
        $this->assertTrue($sorts[1]->isDesc());

        $this->assertSame($sorts[2]->getAttribute(), 'author.name');
        $this->assertTrue($sorts[2]->isAsc());

        $includes = $getResourceCollectionRequest->getInclude();
        $this->assertEquals(['category', 'history', 'purchases'], $includes);

        $fields = $getResourceCollectionRequest->getSparseFieldset();
        $this->assertEquals(['author' => 'name,price,rating', 'book' => 'title,publisher'], $fields);
    }
}
