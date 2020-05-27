<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Exception;

use Exception;

class SchemaCollectionException extends Exception
{
    public static function resourceAlreadyExists(): self
    {
        return new self('Already exists');
    }
}
