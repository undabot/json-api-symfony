<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Exception;

use Exception;

final class PaginationRequiredException extends Exception
{
    public function __construct()
    {
        parent::__construct('Expected pagination, none found.', 422);
    }
}
