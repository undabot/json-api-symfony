<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Exception;

use Exception;

final class QueryParamTransformerNotDefinedException extends Exception
{
    private function __construct(string $message)
    {
        parent::__construct($message, 422);
    }

    /** @param class-string $class */
    public static function forClass(string $class): self
    {
        return new self(sprintf('Query param transformer not defined for %s.', $class));
    }
}
