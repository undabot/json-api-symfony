<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Integration\Http\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\JsonApi\Definition\Model\Request\Pagination\PaginationInterface;
use Undabot\SymfonyJsonApi\Exception\FilterRequiredException;
use Undabot\SymfonyJsonApi\Exception\PaginationRequiredException;
use Undabot\SymfonyJsonApi\Http\Service\Factory\AbstractQueryFactory;
use Undabot\SymfonyJsonApi\Model\Query\Annotation\Query;

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
 * @Query(
 *     class="\Undabot\SymfonyJsonApi\Tests\Integration\Http\Factory\MockQueryWithRequiredPagination",
 *     paginationRequired=true,
 *     filters={
 *          "requiredId"={
 *              "type"="uuid",
 *              "nullable"=false,
 *          },
 *          "optionalId"={
 *              "type"="uuid",
 *              "nullable"=true,
 *          }
 *     }
 * )
 */
final class TestMockQueryWithRequiredPaginationController
{
}

final class AbstractQueryFactoryTest extends TestCase
{
    private MockQueryFactoryImplementation $queryFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryFactory = new MockQueryFactoryImplementation(
            new AnnotationReader(),
        );
    }

    public function testFromRequestWillThrowExceptionIfRequiredPaginationNotProvided(): void
    {
        $request = $this->createMock(GetResourceCollectionRequestInterface::class);

        $request
            ->expects(static::once())
            ->method('getPagination')
            ->willReturn(null);

        $request
            ->expects(static::never())
            ->method('getFilterSet');

        $this->expectException(PaginationRequiredException::class);
        $query = $this->queryFactory->fromRequest($request, TestMockQueryWithRequiredPaginationController::class);

        static::assertInstanceOf(MockQueryWithRequiredPagination::class, $query);
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
        $this->queryFactory->fromRequest($request, TestMockQueryWithRequiredPaginationController::class);
    }
}
