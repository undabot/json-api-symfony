<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Undabot\JsonApi\Model\Request\Pagination\PaginationInterface;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollectionInterface;
use Undabot\SymfonyJsonApi\Model\Collection\PaginatedObjectCollection;

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
    ): ObjectCollectionInterface {
        $doctrinePaginator = $this->paginate($queryBuilder, $pagination, $fetchJoinCollection);

        return PaginatedObjectCollection::createFromDoctrinePaginator($doctrinePaginator);
    }
}
