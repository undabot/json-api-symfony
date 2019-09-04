<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use Countable;
use IteratorAggregate;

interface ObjectCollection extends Countable, IteratorAggregate
{
    public function count(): int;

    /**
     * @return object[]
     */
    public function getItems(): array;
}
