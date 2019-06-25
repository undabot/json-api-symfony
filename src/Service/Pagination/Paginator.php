<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollectionInterface;
use Undabot\SymfonyJsonApi\Model\Collection\PaginatedObjectCollection;

class Paginator implements PaginatorInterface
{
    public function paginate(
        QueryBuilder $queryBuilder,
        int $offset,
        int $size,
        $fetchJoinCollection = true
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
        $fetchJoinCollection = true
    ): ObjectCollectionInterface {
        $doctrinePaginator = $this->paginate($queryBuilder, $offset, $size, $fetchJoinCollection);

        return PaginatedObjectCollection::createFromDoctrinePaginator($doctrinePaginator);
    }
}
