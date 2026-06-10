<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Link;

/** @psalm-immutable */
final class ResponsePaginationLink
{
    public function __construct(
        public string $paginationPageKey,
        public int $nextSet,
        public int $previousSet,
        public int $firstPageKey,
        public ?int $lastPageKey
    ) {}
}
