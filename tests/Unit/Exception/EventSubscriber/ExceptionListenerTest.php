<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Exception\EventSubscriber;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Undabot\JsonApi\Definition\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\JsonApi\Definition\Exception\Request\RequestException;
use Undabot\JsonApi\Implementation\Model\Resource\Resource;
use Undabot\SymfonyJsonApi\Exception\EventSubscriber\ExceptionListener;
use Undabot\SymfonyJsonApi\Http\Model\Response\JsonApiHttpResponse;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Exception\ModelInvalid;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ResourceValidationViolations;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Exception\EventSubscriber\ExceptionListener
 *
 * @medium
 */
final class ExceptionListenerTest extends TestCase
{
    /** @var MockObject */
    private $documentToPhpArrayEncoderInterfaceMock;

    /** @var ExceptionListener */
    private $exceptionListener;

    protected function setUp(): void
    {
        $this->documentToPhpArrayEncoderInterfaceMock = $this->createMock(DocumentToPhpArrayEncoderInterface::class);
        $this->exceptionListener = new ExceptionListener($this->documentToPhpArrayEncoderInterfaceMock);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testOnKernelExceptionWillSetCorrectEventResponseGivenGivenExceptionIsSupported(\Exception $exception): void
    {
        $event = $this->createMock(ExceptionEvent::class);
        $data = [];
        $this->documentToPhpArrayEncoderInterfaceMock
            ->expects(static::once())
            ->method('encode')
            ->willReturn($data);

        $event->expects(static::once())->method('setResponse');

        $this->exceptionListener->onKernelException($event);
    }

    public function exceptionProvider(): \Generator
    {
        yield 'Exception is ModelInvalid instance' => [
            new ModelInvalid(
                $this->createMock(Resource::class),
                new ResourceValidationViolations(
                    new ConstraintViolationList(),
                    new ConstraintViolationList(),
                    new ConstraintViolationList(),
                ),
            ),
        ];

        yield 'Exception is RequestException instance' => [
            new RequestException(),
        ];

        yield 'Exception is Exception instance' => [
            new \Exception(),
        ];
    }

    public function testOnKernelExceptionWillSetCorrectEventResponseGivenGivenExceptionIsSupportedAndEventHaveThrowableMethod(): void
    {
        $event = new DummyThrowableEvent(
            $this->createMock(KernelInterface::class),
            $this->createMock(Request::class),
            KernelInterface::MASTER_REQUEST,
            new \LogicException()
        );
        $data = [];
        $this->documentToPhpArrayEncoderInterfaceMock
            ->expects(static::once())
            ->method('encode')
            ->willReturn($data);

        $this->exceptionListener->onKernelException($event);
        static::assertEquals(
            $event->getResponse(),
            new JsonApiHttpResponse(
                json_encode($data),
                500,
                [
                    'Content-Type' => 'application/vnd.api+json',
                ],
            )
        );
    }
}

class DummyThrowableEvent extends ExceptionEvent
{
    public function getThrowable(): \Throwable
    {
        return new \LogicException();
    }
}
