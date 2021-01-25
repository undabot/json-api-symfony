<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Service\Pagination;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Service\Pagination\PaginationLinkBuilder;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Service\Pagination\PaginationLinkBuilder
 *
 * @small
 */
final class PaginationLinkBuilderTest extends TestCase
{
    private PaginationLinkBuilder $paginationLinkBuilder;

    protected function setUp(): void
    {
        $this->paginationLinkBuilder = new PaginationLinkBuilder();
    }

    public function test(): void
    {

    }
}
