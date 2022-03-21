<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Http\Service\Factory;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\JsonApi\Definition\Model\Request\Pagination\PaginationInterface;
use Undabot\JsonApi\Implementation\Model\Request\Filter\FilterSet;
use Undabot\SymfonyJsonApi\Exception\FilterRequiredException;
use Undabot\SymfonyJsonApi\Exception\PaginationRequiredException;
use Undabot\SymfonyJsonApi\Http\Service\Factory\AbstractQueryFactory;

final class MockQueryFactoryImplementation extends AbstractQueryFactory
{
    protected function getMap(): array
    {
        return [
            UuidInterface::class => [Uuid::class, 'fromString'],
        ];
    }
}

/** @psalm-immutable */
final class MockQueryWithRequiredPagination
{
    public function __construct(
        public int $offset,
        public int $size,
        public UuidInterface $requiredId,
        public ?UuidInterface $optionalId,
    ) {
    }
}

/** @psalm-immutable */
final class MockQueryWithOptionalPagination
{
    public function __construct(
        public ?int $offset,
        public ?int $size,
        public UuidInterface $requiredId,
        public ?UuidInterface $optionalId,
    ) {
    }
}

/**
 * @internal
 * @coversDefaultClass \Undabot\SymfonyJsonApi\Http\Service\Factory\AbstractQueryFactory
 *
 * @small
 */
final class AbstractQueryFactoryTest extends TestCase
{
    private MockQueryFactoryImplementation $queryFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryFactory = new MockQueryFactoryImplementation();
    }

    public function testFromRequestWillThrowExceptionIfRequiredPaginationNotProvided(): void
    {
        $request = $this->createMock(GetResourceCollectionRequestInterface::class);

        $request
            ->expects(static::once())
            ->method('getPagination')
            ->willReturn(null);

        $request
            ->expects(static::once())
            ->method('getFilterSet')
            ->willReturn(null);

        $this->expectException(PaginationRequiredException::class);
        $this->queryFactory->fromRequest($request, MockQueryWithRequiredPagination::class);
    }

    public function testFromRequestWillThrowExceptionIfRequiredFilterNotProvided(): void
    {
        $request = $this->createMock(GetResourceCollectionRequestInterface::class);

        $pagination = $this->createMock(PaginationInterface::class);

        $pagination
            ->expects(static::once())
            ->method('getOffset')
            ->willReturn(0);

        $pagination
            ->expects(static::once())
            ->method('getSize')
            ->willReturn(20);

        $request
            ->expects(static::once())
            ->method('getPagination')
            ->willReturn($pagination);

        $request
            ->expects(static::once())
            ->method('getFilterSet')
            ->willReturn(null);

        $this->expectException(FilterRequiredException::class);
        $this->queryFactory->fromRequest($request, MockQueryWithRequiredPagination::class);
    }

    public function testFromRequestWillAssembleQueryWithRequiredPagination(): void
    {
        $request = $this->createMock(GetResourceCollectionRequestInterface::class);

        $pagination = $this->createMock(PaginationInterface::class);

        $pagination
            ->expects(static::once())
            ->method('getOffset')
            ->willReturn(0);

        $pagination
            ->expects(static::once())
            ->method('getSize')
            ->willReturn(20);

        $request
            ->expects(static::once())
            ->method('getPagination')
            ->willReturn($pagination);

        $filterSet = $this->createMock(FilterSet::class);

        $requiredId = (string) Uuid::uuid4();

        $filterSet
            ->expects(static::exactly(2))
            ->method('getFilterValue')
            ->withConsecutive(['requiredId'], ['optionalId'])
            ->willReturnOnConsecutiveCalls($requiredId, null);

        $request
            ->expects(static::once())
            ->method('getFilterSet')
            ->willReturn($filterSet);

        /** @var MockQueryWithRequiredPagination $query */
        $query = $this->queryFactory->fromRequest($request, MockQueryWithRequiredPagination::class);

        static::assertInstanceOf(MockQueryWithRequiredPagination::class, $query);
        static::assertEquals(0, $query->offset);
        static::assertEquals(20, $query->size);
        static::assertEquals($requiredId, $query->requiredId);
        static::assertNull($query->optionalId);
    }

    public function testFromRequestWillAssembleQueryWithOptionalPagination(): void
    {
        $request = $this->createMock(GetResourceCollectionRequestInterface::class);

        $request
            ->expects(static::once())
            ->method('getPagination')
            ->willReturn(null);

        $filterSet = $this->createMock(FilterSet::class);

        $requiredId = (string) Uuid::uuid4();

        $filterSet
            ->expects(static::exactly(2))
            ->method('getFilterValue')
            ->withConsecutive(['requiredId'], ['optionalId'])
            ->willReturnOnConsecutiveCalls($requiredId, null);

        $request
            ->expects(static::once())
            ->method('getFilterSet')
            ->willReturn($filterSet);

        /** @var MockQueryWithOptionalPagination $query */
        $query = $this->queryFactory->fromRequest($request, MockQueryWithOptionalPagination::class);

        static::assertInstanceOf(MockQueryWithOptionalPagination::class, $query);
        static::assertNull($query->offset);
        static::assertNull($query->size);
        static::assertEquals($requiredId, $query->requiredId);
        static::assertNull($query->optionalId);
    }

    public function testFromRequestWillAssembleQueryWithOptionalPaginationAndDefaultPropertyValues(): void
    {
        $request = $this->createMock(GetResourceCollectionRequestInterface::class);

        $request
            ->expects(static::once())
            ->method('getPagination')
            ->willReturn(null);

        $requiredId = (string) Uuid::uuid4();
        $optionalId = (string) Uuid::uuid4();

        $request
            ->expects(static::once())
            ->method('getFilterSet')
            ->willReturn(null);

        /** @var MockQueryWithOptionalPagination $query */
        $query = $this->queryFactory
            ->withDefaultValueForProperty('requiredId', $requiredId)
            ->withDefaultValueForProperty('optionalId', $optionalId)
            ->fromRequest($request, MockQueryWithOptionalPagination::class);

        static::assertInstanceOf(MockQueryWithOptionalPagination::class, $query);
        static::assertNull($query->offset);
        static::assertNull($query->size);
        static::assertEquals($requiredId, $query->requiredId);
        static::assertEquals($optionalId, $query->optionalId);
    }
}
