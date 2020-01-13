<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Model\Error;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Undabot\SymfonyJsonApi\Model\Error\ValidationViolationError;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Model\Error\ValidationViolationError
 *
 * @small
 */
final class ValidationViolationErrorTest extends TestCase
{
    /** @var MockObject */
    private $violation;

    /** @var ValidationViolationError */
    private $validationViolationError;

    protected function setUp(): void
    {
        $this->violation = $this->createMock(ConstraintViolationInterface::class);

        $this->validationViolationError = new ValidationViolationError($this->violation);
    }

    /**
     * @dataProvider invalidValueProvider
     *
     * @param mixed $invalidValue
     */
    public function testGetDetailWillReturnValidResponseGivenSupportedInvalidValue(
        $invalidValue,
        ?string $expectedReturnValue
    ): void {
        $this->violation->expects(static::once())->method('getInvalidValue')->willReturn($invalidValue);

        static::assertEquals($expectedReturnValue, $this->validationViolationError->getDetail());
    }

    public function invalidValueProvider(): \Generator
    {
        yield 'Null provided' => [
            null,
            null,
        ];

        yield 'String provided' => [
            'foo',
            'foo',
        ];

        $objectWithoutToStringMethod = new \stdClass();
        yield 'Object without toString method provided' => [
            $objectWithoutToStringMethod,
            null,
        ];

        $objectWithToStringMethod = Uuid::uuid4();
        yield 'Object with toString method provided' => [
            $objectWithToStringMethod,
            $objectWithToStringMethod,
        ];

        yield 'Bool provided' => [
            true,
            null,
        ];

        yield 'Int provided' => [
            1,
            null,
        ];

        yield 'Float provided' => [
            1.2,
            null,
        ];
    }
}
