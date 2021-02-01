<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollection;
use Undabot\SymfonyJsonApi\Model\Collection\PaginatedArrayCollection;

class Paginator implements PaginatorInterface
{
    public function paginate(
        QueryBuilder $queryBuilder,
        int $offset,
        int $size,
        bool $fetchJoinCollection = true
    ): DoctrinePaginator {
        $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($size);

        return new DoctrinePaginator($queryBuilder, $fetchJoinCollection);
    }

    public function createPaginatedCollection(
        QueryBuilder $queryBuilder,
        int $offset,
        int $size,
        bool $fetchJoinCollection = true
    ): ObjectCollection {
        $doctrinePaginator = $this->paginate($queryBuilder, $offset, $size, $fetchJoinCollection);

        return PaginatedArrayCollection::createFromDoctrinePaginator($doctrinePaginator);
    }
}
