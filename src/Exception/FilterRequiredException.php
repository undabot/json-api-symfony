<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Exception;

use Exception;

final class FilterRequiredException extends Exception
{
    private function __construct(string $message)
    {
        parent::__construct($message, 422);
    }

    public static function withName(string $name): self
    {
        return new self(sprintf('Filter %s is required.', $name));
    }
}
