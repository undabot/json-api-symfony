<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Pagination;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Model\Request\Pagination\OffsetBasedPagination;
use Undabot\SymfonyJsonApi\Http\Service\Factory\PaginationFactory;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class OffsetBasedPaginationFactoryTest extends TestCase
{
    /** @var PaginationFactory */
    private $paginationFactory;

    protected function setUp(): void
    {
        $this->paginationFactory = new PaginationFactory();
    }

    /** @dataProvider validPageBasedPaginationParamsProvider */
    public function testPaginationFactoryCanCreateOffsetBasedPaginationFromValidParams($params): void
    {
        $pagination = $this->paginationFactory->fromArray($params);

        static::assertInstanceOf(OffsetBasedPagination::class, $pagination);
    }

    /** @dataProvider invalidPaginationParamsProvider */
    public function testPaginationFactoryWillThrowExceptionForInvalidParams(array $invalidParams): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->paginationFactory->fromArray($invalidParams);
    }

    public function invalidPaginationParamsProvider()
    {
        return [
            [
                [],
            ],
            [
                ['limit' => 10],
            ],
            [
                ['offset' => 2],
            ],
            [
                [
                    'limit' => '0',
                    'offset' => '0',
                ],
            ],
            [
                [
                    'limit' => 0,
                    'offset' => 0,
                ],
            ],
            [
                [
                    'limit' => null,
                    'offset' => null,
                ],
            ],
            [
                [
                    'limit' => 10.1,
                    'offset' => 2.1,
                ],
            ],
            [
                [
                    'limit' => 10.0,
                    'offset' => 2.0,
                ],
            ],
        ];
    }

    public function validPageBasedPaginationParamsProvider()
    {
        return [
            [
                [
                    'limit' => 10,
                    'offset' => 2,
                ],
            ],
            [
                [
                    'limit' => '10',
                    'offset' => '2',
                ],
            ],
            [
                [
                    'limit' => 10,
                    'offset' => '2',
                ],
            ],
            [
                [
                    'limit' => '10',
                    'offset' => 2,
                ],
            ],
        ];
    }

    public function testGetPageNumberWillReturnCorrectNumber(): void
    {
        $params = [
            'offset' => 3,
            'limit' => 10,
        ];

        /** @var OffsetBasedPagination $pagination */
        $pagination = $this->paginationFactory->fromArray($params);

        static::assertSame(3, $pagination->getOffset());
        static::assertSame(10, $pagination->getSize());
    }
}
