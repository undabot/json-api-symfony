<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Query;

use Assert\Assertion;
use Undabot\JsonApi\Implementation\Model\Request\Sort\Sort;

/** @psalm-immutable */
final class QuerySort
{
    public function __construct(
        public string $sortName,
        public string $propertyName,
        public string $value
    ) {
        Assertion::inArray(
            mb_strtoupper($value),
            [
                Sort::SORT_ORDER_ASC,
                Sort::SORT_ORDER_DESC,
            ],
        );
    }
}
