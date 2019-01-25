<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Undabot\JsonApi\Model\Request\Pagination\PaginationInterface;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectListCollection;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectListCollectionInterface;

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
        $count = $doctrinePaginator->count();
        $entities = $doctrinePaginator->getQuery()->getResult();

        return new ObjectListCollection($entities, $count);
    }
}
