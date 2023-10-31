<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatedArrayCollection extends ArrayCollection
{
    public static function createFromDoctrinePaginator(Paginator $paginator): self
    {
        $count = $paginator->count();
        $entities = $paginator->getQuery()->getResult();


        return new self(is_array($entities) ? $entities : (array) $entities, $count);
    }
}
