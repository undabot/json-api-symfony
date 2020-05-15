<?php

declare(strict_types=1);

namespace Undabot\JsonApi\Tests\Unit\Http\Service\EventSubscriber;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Undabot\JsonApi\Definition\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Error\ErrorCollection;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceDeletedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceValidationErrorsResponse;
use Undabot\SymfonyJsonApi\Http\Service\EventSubscriber\ViewResponseSubscriber;

/**
 * @internal
 * @covers \Undabot\SymfonyJsonApi\Http\Service\EventSubscriber\ViewResponseSubscriber
 *
 * @medium
 */
final class ViewResponseSubscriberTest extends TestCase
{
    /** @var MockObject */
    private $documentEncoder;

    /** @var ViewResponseSubscriber */
    private $viewResponseSubscriber;

    protected function setUp(): void
    {
        $this->documentEncoder = $this->createMock(DocumentToPhpArrayEncoderInterface::class);
        $this->viewResponseSubscriber = new ViewResponseSubscriber($this->documentEncoder);
    }

    /**
     * @dataProvider controllerResultProvider
     */
    public function testBuildViewWillSetCorrectResponseInEventGivenValidControllerResult(
        object $controllerResult,
        bool $shouldEncode
    ): void {
        $event = $this->createMock(ViewEvent::class);
        $event->expects(static::once())->method('getControllerResult')->willReturn($controllerResult);
        $event->expects(static::once())->method('setResponse');
        if ($shouldEncode) {
            $this->documentEncoder->expects(static::once())->method('encode')->willReturn(['foo' => 'bar']);
        } else {
            $this->documentEncoder->expects(static::never())->method('encode');
        }

        $this->viewResponseSubscriber->buildView($event);
    }

    public function controllerResultProvider(): \Generator
    {
        yield 'ResourceCollectionResponse returned by controller' => [
            new ResourceCollectionResponse($this->createMock(ResourceCollectionInterface::class)),
            true,
        ];

        yield 'ResourceCreatedResponse returned by controller' => [
            new ResourceCreatedResponse($this->createMock(ResourceInterface::class)),
            true,
        ];

        yield 'ResourceUpdatedResponse returned by controller' => [
            new ResourceUpdatedResponse($this->createMock(ResourceInterface::class)),
            true,
        ];

        yield 'ResourceDeletedResponse returned by controller' => [
            new ResourceDeletedResponse(),
            false,
        ];

        yield 'ResourceResponse returned by controller' => [
            new ResourceResponse($this->createMock(ResourceInterface::class)),
            true,
        ];

        yield 'ResourceValidationErrorsResponse returned by controller' => [
            new ResourceValidationErrorsResponse(new ErrorCollection([])),
            true,
        ];
    }

    public function testBuildViewWillNotSetResponseInEventGivenValidControllerResultButUnsupportedControllerResult(): void
    {
        $event = $this->createMock(ViewEvent::class);
        $event->expects(static::once())->method('getControllerResult')->willReturn(new ResourceCollection([]));
        $event->expects(static::never())->method('setResponse');
        $this->documentEncoder->expects(static::never())->method('encode');

        $this->viewResponseSubscriber->buildView($event);
    }
}
