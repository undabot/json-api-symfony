<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use Countable;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<array<mixed,mixed>>
 */
interface ObjectCollection extends Countable, IteratorAggregate
{
    public function count(): int;

    /**
     * @return object[]
     */
    public function getItems(): array;
}
