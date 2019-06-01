<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Undabot\JsonApi\Model\Request\Pagination\PaginationInterface;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectListCollectionInterface;
use Undabot\SymfonyJsonApi\Model\Collection\PaginatedObjectListCollection;

class Paginator implements PaginatorInterface
{
    public function paginate(
        QueryBuilder $queryBuilder,
        PaginationInterface $pagination,
        $fetchJoinCollection = true
    ): DoctrinePaginator {
        $queryBuilder
            ->setFirstResult($pagination->getOffset())
            ->setMaxResults($pagination->getSize());

        return new DoctrinePaginator($queryBuilder, $fetchJoinCollection);
    }

    public function createPaginatedListCollection(
        QueryBuilder $queryBuilder,
        PaginationInterface $pagination,
        $fetchJoinCollection = true
    ): ObjectListCollectionInterface {
        $doctrinePaginator = $this->paginate($queryBuilder, $pagination, $fetchJoinCollection);

        return PaginatedObjectListCollection::createFromDoctrinePaginator($doctrinePaginator);
    }
}
