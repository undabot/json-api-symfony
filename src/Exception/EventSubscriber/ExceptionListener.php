<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Exception\EventSubscriber;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Undabot\JsonApi\Definition\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\JsonApi\Definition\Exception\Request\ClientGeneratedIdIsNotAllowedException;
use Undabot\JsonApi\Definition\Exception\Request\RequestException;
use Undabot\JsonApi\Implementation\Model\Document\Document;
use Undabot\JsonApi\Implementation\Model\Error\Error;
use Undabot\JsonApi\Implementation\Model\Error\ErrorCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\JsonApiHttpResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceValidationErrorsResponse;
use Undabot\SymfonyJsonApi\Model\Resource\Exception\ResourceIdValueMismatch;
use Undabot\SymfonyJsonApi\Model\Resource\Exception\ResourceTypeValueMismatch;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Exception\ModelInvalid;

class ExceptionListener
{
    /** @var DocumentToPhpArrayEncoderInterface */
    private $documentToPhpArrayEncoderInterface;

    public function __construct(DocumentToPhpArrayEncoderInterface $documentToPhpArrayEncoderInterface)
    {
        $this->documentToPhpArrayEncoderInterface = $documentToPhpArrayEncoderInterface;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ModelInvalid) {
            $responseModel = ResourceValidationErrorsResponse::fromException($exception);
            $document = new Document(null, $responseModel->getErrorCollection());
            $data = $this->documentToPhpArrayEncoderInterface->encode($document);
            $response = JsonApiHttpResponse::validationError($data);
            $event->setResponse($response);

            return;
        }

        if (
            ($exception instanceof ClientGeneratedIdIsNotAllowedException)
            || ($exception instanceof ResourceIdValueMismatch)
            || ($exception instanceof ResourceTypeValueMismatch)
        ) {
            $errorCollection = new ErrorCollection([
                $this->buildError($exception),
            ]);
            $document = new Document(null, $errorCollection);
            $data = $this->documentToPhpArrayEncoderInterface->encode($document);
            $response = JsonApiHttpResponse::forbidden($data);
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof RequestException) {
            $errorCollection = new ErrorCollection([
                $this->buildError($exception),
            ]);
            $document = new Document(null, $errorCollection);
            $data = $this->documentToPhpArrayEncoderInterface->encode($document);
            $response = JsonApiHttpResponse::badRequest($data);
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $errorCollection = new ErrorCollection([
                $this->buildError($exception),
            ]);
            $document = new Document(null, $errorCollection);
            $data = $this->documentToPhpArrayEncoderInterface->encode($document);
            $response = JsonApiHttpResponse::fromSymfonyHttpException($data, $exception);
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof \Exception) {
            $errorCollection = new ErrorCollection([
                $this->buildError($exception),
            ]);
            $document = new Document(null, $errorCollection);
            $data = $this->documentToPhpArrayEncoderInterface->encode($document);
            $response = JsonApiHttpResponse::serverError($data);
            $event->setResponse($response);

            return;
        }
    }

    private function buildError(\Throwable $exception): Error
    {
        return new Error(
            null,
            null,
            null,
            null,
            $exception->getMessage(),
            sprintf(
                'Exception %s: "%s"',
                \get_class($exception),
                $exception->getMessage()
            )
        );
    }
}
