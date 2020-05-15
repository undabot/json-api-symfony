<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Http\Service\ParamConverter;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Undabot\SymfonyJsonApi\Exception\ParamConverterInvalidUuidFormatException;
use Undabot\SymfonyJsonApi\Http\Service\ParamConverter\UuidConverter;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class UuidConverterTest extends TestCase
{
    private UuidConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new UuidConverter();
    }

    public function testSupports(): void
    {
        $config = $this->createConfiguration(UuidInterface::class);
        static::assertTrue($this->converter->supports($config));

        $config = $this->createConfiguration(__CLASS__);
        static::assertFalse($this->converter->supports($config));

        $config = $this->createConfiguration();
        static::assertFalse($this->converter->supports($config));
    }

    public function testApply(): void
    {
        $request = new Request([], [], ['id' => '47ce6a0c-9cb0-4332-a831-980c43922b00']);
        $config = $this->createConfiguration(UuidInterface::class, 'id');

        $this->converter->apply($request, $config);

        static::assertInstanceOf(UuidInterface::class, $request->attributes->get('id'));
        static::assertEquals('47ce6a0c-9cb0-4332-a831-980c43922b00', (string) $request->attributes->get('id'));
    }

    public function testApplyWithInvalidUuid(): void
    {
        $this->expectException(ParamConverterInvalidUuidFormatException::class);
        $this->expectExceptionMessage('Invalid UUID string: nan');
        $request = new Request([], [], ['id' => 'nan']);
        $config = $this->createConfiguration(UuidInterface::class, 'id');

        $this->converter->apply($request, $config);
    }

    private function createConfiguration($class = null, $name = null)
    {
        $config = $this
            ->getMockBuilder(ParamConverter::class)
            ->setMethods(['getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray', 'isOptional'])
            ->disableOriginalConstructor()
            ->getMock();

        if (null !== $name) {
            $config->expects(static::any())
                ->method('getName')
                ->willReturn($name);
        }
        if (null !== $class) {
            $config->expects(static::any())
                ->method('getClass')
                ->willReturn($class);
        }

        return $config;
    }
}
