<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Helper;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\TypeHelper;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
#[CoversNothing]
#[Small]
final class TypeHelperTest extends TestCase
{
    public function testItResolvesBoolToBoolean(): void
    {
        self::assertSame(
            'boolean',
            TypeHelper::resolve('bool')
        );
    }

    public function testItResolvesIntToInteger(): void
    {
        self::assertSame(
            'integer',
            TypeHelper::resolve('int')
        );
    }

    public function testItResolvesFloatToNumber(): void
    {
        self::assertSame(
            'number',
            TypeHelper::resolve('float')
        );
    }

    public function testItReturnsPassedValueWhenUnsupported(): void
    {
        self::assertSame(
            'somethingThatDoesntExist',
            TypeHelper::resolve('somethingThatDoesntExist')
        );
    }
}
