<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper;

class TypeHelper
{
    private static array $map = [
        'bool' => 'boolean',
        'int' => 'integer',
        'float' => 'number',
    ];

    public static function resolve(string $type): string
    {
        return self::$map[$type] ?? $type;
    }
}
