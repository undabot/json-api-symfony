<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Query;

use Undabot\SymfonyJsonApi\Exception\FilterRequiredException;

/** @psalm-immutable */
final class QueryFilter
{
    public function __construct(
        public string $filterName,
        public string $propertyName,
        public string $type,
        public bool $nullable,
        public mixed $value
    ) {
        if (false === $nullable && null === $value) {
            throw FilterRequiredException::withName($filterName);
        }
    }
}
