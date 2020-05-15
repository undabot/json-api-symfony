<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface PaginatorInterface
{
    public function paginate(
        QueryBuilder $queryBuilder,
        int $offset,
        int $size,
        bool $fetchJoinCollection
    ): Paginator;
}
