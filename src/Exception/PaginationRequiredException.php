<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Exception;

use Exception;

final class PaginationRequiredException extends Exception
{
    private function __construct(string $message)
    {
        parent::__construct($message, 422);
    }

    public static function noPaginationProvided(): self
    {
        return new self('Expected pagination, none found.');
    }
}
