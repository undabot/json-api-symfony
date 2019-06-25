<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Collection;

use Countable;
use IteratorAggregate;

interface ObjectCollectionInterface extends Countable, IteratorAggregate
{
    public function count(): int;

    public function getItems(): array;
}
