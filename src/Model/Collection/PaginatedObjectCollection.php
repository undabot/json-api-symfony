<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatedObjectListCollection extends ObjectListCollection
{
    public static function createFromDoctrinePaginator(Paginator $paginator)
    {
        $count = $paginator->count();
        $entities = $paginator->getQuery()->getResult();

        return new self($entities, $count);
    }
}
