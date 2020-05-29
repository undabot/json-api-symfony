<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Exception;

use Exception;

class ResourceApiEndpointsException extends Exception
{
    public static function collectionNotEnabled(): self
    {
        return new self('Enable collection endpoint');
    }

    public static function singleNotEnabled(): self
    {
        return new self('Enable single endpoint');
    }
}
