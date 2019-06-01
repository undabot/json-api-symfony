<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Sort;

use Doctrine\ORM\QueryBuilder;
use Undabot\JsonApi\Model\Request\Sort\SortSet;

interface SorterInterface
{
    public function sort(QueryBuilder $queryBuilder, SortSet $sortSet, string $alias): void;
}
