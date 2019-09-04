<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Request;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Encoding\PhpArrayToResourceEncoderInterface;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Service\Factory\RequestFactory;
use Undabot\SymfonyJsonApi\Http\Service\Validation\RequestValidator;

class GetResourceCollectionRequestTest extends TestCase
{
    /** @var RequestFactory|MockObject */
    private $requestFactoryMock;

    /** @var Request|MockObject */
    private $requestMock;

    /** @var ParameterBag|MockObject */
    private $parameterBagMock;

    protected function setUp()
    {
        $this->requestMock = $this->createMock(Request::class);
        $this->parameterBagMock = $this->createMock(ParameterBag::class);
        $this->requestMock->query = $this->parameterBagMock;

        $resourceEncoder = $this->createMock(PhpArrayToResourceEncoderInterface::class);
        $requestValidatorMock = $this->createMock(RequestValidator::class);
        $this->requestFactoryMock = new RequestFactory(
            $resourceEncoder,
            $requestValidatorMock
        );
    }


    public function testRequestWithoutAnyParametersCanBeConstructed()
    {
//        $parameterBagMock = $this->createMock(ParameterBag::class);
//        $requestMock = $this->createMock(Request::class);
        $this->parameterBagMock->method('get')->willReturn(null);

        $getResourceCollectionRequest = $this->requestFactoryMock->getResourceCollectionRequest($this->requestMock);
        $this->assertInstanceOf(GetResourceCollectionRequest::class, $getResourceCollectionRequest);

        $this->assertNull($getResourceCollectionRequest->getPagination());
        $this->assertNull($getResourceCollectionRequest->getIncludes());
        $this->assertNull($getResourceCollectionRequest->getFilterSet());
        $this->assertNull($getResourceCollectionRequest->getSortSet());
        $this->assertNull($getResourceCollectionRequest->getSparseFieldset());
    }

    public function testRequestWithAllValidParametersCanBeConstructed()
    {
//        $parameterBagMock = $this->createMock(ParameterBag::class);
//        $requestMock = $this->createMock(Request::class);

        $queryParamsMap = [
            ['page', null, ['number' => 3, 'size' => 10]],
            ['filter', null, ['priceMin' => 3, 'priceMax' => 10.5, 'name' => 'John']],
            ['sort', null, 'name,-price,author.name'],
            ['include', null, 'category,history,purchases'],
            ['fields', null, ['author' => 'name,price,rating', 'book' => 'title,publisher']],
        ];

        $this->parameterBagMock->method('get')->will($this->returnValueMap($queryParamsMap));

        $this->parameterBagMock->method('has')->will($this->returnCallback(function ($param) use ($queryParamsMap) {
            $filtered = array_filter($queryParamsMap, function ($item) use ($param) {
                return ($item[0] ?? null) === $param;
            });

            return count($filtered) === 1;
        }));
        $this->requestMock->query = $this->parameterBagMock;

        $getResourceCollectionRequest = $this->requestFactoryMock->getResourceCollectionRequest($this->requestMock);
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

        $includes = $getResourceCollectionRequest->getIncludes();
        $this->assertEquals(['category', 'history', 'purchases'], $includes);

        $fields = $getResourceCollectionRequest->getSparseFieldset();
        $this->assertEquals(['author' => 'name,price,rating', 'book' => 'title,publisher'], $fields);
    }
}
