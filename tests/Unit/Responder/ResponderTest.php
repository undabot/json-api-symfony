<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Responder;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceDeletedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\EncoderInterface;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class ResponderTest extends TestCase
{
    public function testReturnInstanceOfResourceResponse(): void
    {
        $emMock = $this->createMock(EntityManagerInterface::class);
        $dataEncoderMock = $this->createMock(EncoderInterface::class);

        $responder = new TestResponder($emMock, $dataEncoderMock);

        $result = $responder->resource(new \stdClass());

        static::assertInstanceOf(ResourceResponse::class, $result);
    }

    public function testReturnInstanceOfResourceCollectionResponse(): void
    {
        $emMock = $this->createMock(EntityManagerInterface::class);
        $dataEncoderMock = $this->createMock(EncoderInterface::class);

        $responder = new TestResponder($emMock, $dataEncoderMock);

        $result = $responder->resourceCollection([new \stdClass()]);

        static::assertInstanceOf(ResourceCollectionResponse::class, $result);
    }

    public function testReturnInstanceOfResourceUpdatedResponse(): void
    {
        $emMock = $this->createMock(EntityManagerInterface::class);
        $dataEncoderMock = $this->createMock(EncoderInterface::class);

        $responder = new TestResponder($emMock, $dataEncoderMock);

        $result = $responder->resourceUpdated(new \stdClass());

        static::assertInstanceOf(ResourceUpdatedResponse::class, $result);
    }

    public function testReturnInstanceOfResourceCreatedResponse(): void
    {
        $emMock = $this->createMock(EntityManagerInterface::class);
        $dataEncoderMock = $this->createMock(EncoderInterface::class);

        $responder = new TestResponder($emMock, $dataEncoderMock);

        $result = $responder->resourceCreated(new \stdClass());

        static::assertInstanceOf(ResourceCreatedResponse::class, $result);
    }

    public function testReturnInstanceOfResourceDeletedResponse(): void
    {
        $emMock = $this->createMock(EntityManagerInterface::class);
        $dataEncoderMock = $this->createMock(EncoderInterface::class);

        $responder = new TestResponder($emMock, $dataEncoderMock);

        $result = $responder->resourceDeleted();

        static::assertInstanceOf(ResourceDeletedResponse::class, $result);
    }
}
