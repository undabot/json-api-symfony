<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Http\Service\EventSubscriber;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Undabot\JsonApi\Definition\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Error\ErrorCollection;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceDeletedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceValidationErrorsResponse;
use Undabot\SymfonyJsonApi\Http\Service\EventSubscriber\ViewResponseSubscriber;

#[CoversClass(ViewResponseSubscriber::class)]
#[Medium]
final class ViewResponseSubscriberTest extends TestCase
{
    private MockObject $documentEncoderMock;
    private ViewResponseSubscriber $viewResponseSubscriber;

    protected function setUp(): void
    {
        $this->documentEncoderMock = $this->createMock(DocumentToPhpArrayEncoderInterface::class);
        $this->viewResponseSubscriber = new ViewResponseSubscriber($this->documentEncoderMock);
    }

    public function buildEvent(
        object $controllerResult,
    ): ViewEvent {
        $event = new ViewEvent(
            new HttpKernel(new EventDispatcher(), new ControllerResolver()),
            Request::create('http://localhost:8000/web/v1/posts'),
            HttpKernelInterface::MAIN_REQUEST,
            $controllerResult,
        );

        $this->viewResponseSubscriber->buildView($event);

        return $event;
    }

    public function testBuildViewWillSetCorrectResponseInEventGivenValidResourceCollectionResponse(): void
    {
        $event = $this->buildEvent(new ResourceCollectionResponse($this->createMock(ResourceCollectionInterface::class)));
        self::assertSame('null', $event->getResponse()?->getContent());
    }

    public function testBuildViewWillSetCorrectResponseInEventGivenValidResourceCreatedResponse(): void
    {
        $event = $this->buildEvent(new ResourceCollectionResponse($this->createMock(ResourceCollectionInterface::class)));
        self::assertSame('null', $event->getResponse()?->getContent());
    }

    public function testBuildViewWillSetCorrectResponseInEventGivenValidResourceUpdatedResponse(): void
    {
        $event = $this->buildEvent(new ResourceUpdatedResponse($this->createMock(ResourceInterface::class)));
        self::assertSame('null', $event->getResponse()?->getContent());
    }

    public function testBuildViewWillSetCorrectResponseInEventGivenValidResourceDeletedResponse(): void
    {
        $event = $this->buildEvent(new ResourceDeletedResponse());
        self::assertSame('', $event->getResponse()?->getContent());
    }

    public function testBuildViewWillSetCorrectResponseInEventGivenValidResourceResponse(): void
    {
        $event = $this->buildEvent(new ResourceResponse($this->createMock(ResourceInterface::class)));
        self::assertSame('null', $event->getResponse()?->getContent());
    }

    public function testBuildViewWillSetCorrectResponseInEventGivenValidNullResourceResponse(): void
    {
        $event = $this->buildEvent(new ResourceResponse(null));
        self::assertSame('null', $event->getResponse()?->getContent());
    }

    public function testBuildViewWillSetCorrectResponseInEventGivenValidResourceValidationErrorsResponse(): void
    {
        $event = $this->buildEvent(new ResourceValidationErrorsResponse(new ErrorCollection([])));
        self::assertSame('null', $event->getResponse()?->getContent());
    }

    public function testBuildViewWillNotSetResponseInEventGivenValidControllerResultButUnsupportedControllerResult(): void
    {
        $event = $this->buildEvent(new ResourceCollection([]));
        self::assertNull($event->getResponse()?->getContent());
    }
}
