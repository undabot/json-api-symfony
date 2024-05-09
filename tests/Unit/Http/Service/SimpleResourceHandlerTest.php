<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Http\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Undabot\JsonApi\Definition\Model\Request\ResourcePayloadRequest;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Http\Service\SimpleResourceHandler;
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Service\Resource\Denormalizer\ResourceDenormalizer;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Exception\ModelInvalid;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidationViolations;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidator;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
#[CoversClass('\Undabot\SymfonyJsonApi\Http\Service\SimpleResourceHandler')]
#[Small]
final class SimpleResourceHandlerTest extends TestCase
{
    /** @var MockObject */
    private $validator;

    /** @var MockObject */
    private $denormalizer;

    /** @var SimpleResourceHandler */
    private $simpleResourceHandler;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ResourceValidator::class);
        $this->denormalizer = $this->createMock(ResourceDenormalizer::class);

        $this->simpleResourceHandler = new SimpleResourceHandler($this->validator, $this->denormalizer);
    }

    public function testGetModelFromRequestWillReturnValidModelGivenValidRequestAndClass(): void
    {
        $resourcePayloadRequest = $this->createMock(ResourcePayloadRequest::class);

        $resourcePayloadRequest->expects(self::once())
            ->method('getResource')
            ->willReturn($this->createMock(ResourceInterface::class));

        $this->validator->expects(self::once())->method('assertValid');

        $apiModel = $this->createMock(ApiModel::class);

        $this->denormalizer->expects(self::once())->method('denormalize')->willReturn($apiModel);

        $this->simpleResourceHandler->getModelFromRequest($resourcePayloadRequest, FooApiModel::class);
    }

    public function testGetModelFromRequestWillThrowExceptionGivenClassIsNotInstanceOfApiModel(): void
    {
        $this->expectException(ModelInvalid::class);
        $this->expectExceptionMessage('');

        $resourcePayloadRequest = $this->createMock(ResourcePayloadRequest::class);

        $resource = $this->createMock(ResourceInterface::class);

        $resourcePayloadRequest->expects(self::once())
            ->method('getResource')
            ->willReturn($resource);

        $this->validator
            ->expects(self::once())
            ->method('assertValid')
            ->willThrowException(new ModelInvalid($resource, $this->createMock(ResourceValidationViolations::class)));

        $this->denormalizer->expects(self::never())->method('denormalize');

        $this->simpleResourceHandler->getModelFromRequest($resourcePayloadRequest, FooApiModel::class);
    }
}

class FooApiModel implements ApiModel {}
