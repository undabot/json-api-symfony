<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Http\Service\ArgumentResolver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Undabot\SymfonyJsonApi\Exception\InvalidUuidFormatException;
use Undabot\SymfonyJsonApi\Http\Service\ArgumentResolver\UuidValueResolver;

/**
 * @internal
 */
#[CoversClass(UuidValueResolver::class)]
#[Small]
final class UuidValueResolverTest extends TestCase
{
    private UuidValueResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new UuidValueResolver();
    }

    public function testResolveIgnoresNonUuidArgumentTypes(): void
    {
        $request = new Request([], [], ['id' => '47ce6a0c-9cb0-4332-a831-980c43922b00']);

        self::assertSame([], [...$this->resolver->resolve($request, $this->createArgument(self::class))]);
        self::assertSame([], [...$this->resolver->resolve($request, $this->createArgument(null))]);
    }

    public function testResolveResolvesUuidFromRequestAttributes(): void
    {
        $request = new Request([], [], ['id' => '47ce6a0c-9cb0-4332-a831-980c43922b00']);

        $resolved = [...$this->resolver->resolve($request, $this->createArgument(UuidInterface::class))];

        self::assertCount(1, $resolved);
        self::assertInstanceOf(UuidInterface::class, $resolved[0]);
        self::assertSame('47ce6a0c-9cb0-4332-a831-980c43922b00', (string) $resolved[0]);
    }

    public function testResolveThrowsForInvalidUuid(): void
    {
        $request = new Request([], [], ['id' => 'nan']);

        $this->expectException(InvalidUuidFormatException::class);
        $this->expectExceptionMessage('Invalid UUID string: nan');

        $this->resolver->resolve($request, $this->createArgument(UuidInterface::class));
    }

    private function createArgument(?string $type): ArgumentMetadata
    {
        return new ArgumentMetadata('id', $type, false, false, null);
    }
}
