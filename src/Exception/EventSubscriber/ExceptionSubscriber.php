<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Exception\EventSubscriber;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Undabot\JsonApi\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\JsonApi\Exception\Request\RequestException;
use Undabot\JsonApi\Model\Document\Document;
use Undabot\JsonApi\Model\Error\Error;
use Undabot\JsonApi\Model\Error\ErrorCollection;
use Undabot\SymfonyJsonApi\Http\Model\Response\JsonApiHttpResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceValidationErrorsResponse;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Exception\ModelInvalid;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var DocumentToPhpArrayEncoderInterface */
    private $documentToPhpArrayEncoderInterface;

    public function __construct(DocumentToPhpArrayEncoderInterface $documentToPhpArrayEncoderInterface)
    {
        $this->documentToPhpArrayEncoderInterface = $documentToPhpArrayEncoderInterface;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['buildErrorResponse', -128],
        ];
    }

    public function buildErrorResponse(ExceptionEvent $event): void
    {
        $exception = $event->getException();

        if ($exception instanceof ModelInvalid) {
            $responseModel = ResourceValidationErrorsResponse::fromException($exception);
            $document = new Document(null, $responseModel->getErrorCollection());
            $data = $this->documentToPhpArrayEncoderInterface->encode($document);
            $response = JsonApiHttpResponse::validationError($data);
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

//        if ($exception instanceof InvalidRequestContentTypeHeaderException) {
//            $response = new UnsupportedMediaTypeJsonApiResponse($exception);
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof InvalidRequestAcceptHeaderException) {
//            $response = new NotAcceptableJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof InvalidRequestDataException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof UnsupportedQueryStringParameterGivenException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof ClientGeneratedIdIsNotAllowedException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof UnsupportedFilterAttributeGivenException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof UnsupportedIncludeValuesGivenException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof UnsupportedPaginationRequestedException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof UnsupportedQueryStringParameterGivenException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof UnsupportedSortRequestedException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof UnsupportedSparseFieldsetRequestedException) {
//            $response = new BadRequestJsonApiResponse($exception->getMessage());
//            $event->setResponse($response);
//
//            return;
//        }
//
//        if ($exception instanceof UnsupportedMediaTypeException) {
//            $response = new UnsupportedMediaTypeJsonApiResponse($exception);
//            $event->setResponse($response);
//
//            return;
//        }
    }

    private function buildError(\Exception $exception): Error
    {
        $e = FlattenException::create($exception);

        return new Error(
            null,
            null,
            null,
            null,
            $e->getMessage(),
            sprintf(
                'Exception %s: "%s"',
                $e->getClass(),
                $e->getMessage()
            )
        );
    }
}
