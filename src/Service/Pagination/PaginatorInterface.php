<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Undabot\JsonApi\Model\Request\Pagination\PaginationInterface;

interface PaginatorInterface
{
    public function paginate(QueryBuilder $queryBuilder, PaginationInterface $pagination): Paginator;
}
