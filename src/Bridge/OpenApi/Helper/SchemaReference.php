<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\NamedSchema;

class SchemaReference
{
    public static function schema(NamedSchema $schema): string
    {
        return static::ref($schema->getName());
    }

    public static function ref(string $referenceId): string
    {
        return '#/components/schemas/' . $referenceId;
    }
}
