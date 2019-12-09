<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Helper;

use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Helper\TypeHelper;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;

class TypeHelperTest extends TestCase
{
    public function testItResolvesBoolToBoolean()
    {
        $this->assertSame(
            'boolean',
            TypeHelper::resolve('bool')
        );
    }

    public function testItResolvesIntToInteger()
    {
        $this->assertSame(
            'integer',
            TypeHelper::resolve('int')
        );
    }

    public function testItResolvesFloatToNumber()
    {
        $this->assertSame(
            'number',
            TypeHelper::resolve('float')
        );
    }

    public function testItReturnsPassedValueWhenUnsupported()
    {
        $this->assertSame(
            'somethingThatDoesntExist',
            TypeHelper::resolve('somethingThatDoesntExist')
        );
    }
}
