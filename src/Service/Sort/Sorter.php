<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Sort;

use Doctrine\ORM\QueryBuilder;
use Undabot\JsonApi\Model\Request\Sort\Sort;
use Undabot\JsonApi\Model\Request\Sort\SortSet;

class Sorter implements SorterInterface
{
    const SORT_ASCENDING = 'ASC';
    const SORT_DESCENDING = 'DESC';

    public function sort(QueryBuilder $queryBuilder, SortSet $sortSet, string $alias): void
    {
        /** @var Sort $sort */
        foreach ($sortSet as $sort) {
            $attributeName = sprintf('%s.%s', $alias, $sort->getAttribute());

            if (true === $sort->isAsc()) {
                $queryBuilder->addOrderBy($attributeName, self::SORT_ASCENDING);
            }

            if (true === $sort->isDesc()) {
                $queryBuilder->addOrderBy($attributeName, self::SORT_DESCENDING);
            }
        }
    }
}
