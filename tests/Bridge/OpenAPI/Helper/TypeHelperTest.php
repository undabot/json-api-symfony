<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Helper;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\TypeHelper;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class TypeHelperTest extends TestCase
{
    public function testItResolvesBoolToBoolean(): void
    {
        static::assertSame(
            'boolean',
            TypeHelper::resolve('bool')
        );
    }

    public function testItResolvesIntToInteger(): void
    {
        static::assertSame(
            'integer',
            TypeHelper::resolve('int')
        );
    }

    public function testItResolvesFloatToNumber(): void
    {
        static::assertSame(
            'number',
            TypeHelper::resolve('float')
        );
    }

    public function testItReturnsPassedValueWhenUnsupported(): void
    {
        static::assertSame(
            'somethingThatDoesntExist',
            TypeHelper::resolve('somethingThatDoesntExist')
        );
    }
}
