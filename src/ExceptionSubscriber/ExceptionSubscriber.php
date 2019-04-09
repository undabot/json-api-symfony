<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\ExceptionSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Undabot\SymfonyJsonApi\Request\Exception\ClientGeneratedIdIsNotAllowedException;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestAcceptHeaderException;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestContentTypeHeaderException;
use Undabot\SymfonyJsonApi\Request\Exception\InvalidRequestDataException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedFilterAttributeGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedIncludeValuesGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedMediaTypeException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedPaginationRequestedException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedQueryStringParameterGivenException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedSortRequestedException;
use Undabot\SymfonyJsonApi\Request\Exception\UnsupportedSparseFieldsetRequestedException;
use Undabot\SymfonyJsonApi\Response\BadRequestJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\NotAcceptableJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\UnsupportedMediaTypeJsonApiResponse;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['buildErrorResponse', -63],
        ];
    }

    public function buildErrorResponse(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof InvalidRequestContentTypeHeaderException) {
            $response = new UnsupportedMediaTypeJsonApiResponse($exception);
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof InvalidRequestAcceptHeaderException) {
            $response = new NotAcceptableJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof InvalidRequestDataException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof UnsupportedQueryStringParameterGivenException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof ClientGeneratedIdIsNotAllowedException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof UnsupportedFilterAttributeGivenException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof UnsupportedIncludeValuesGivenException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof UnsupportedPaginationRequestedException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof UnsupportedQueryStringParameterGivenException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof UnsupportedSortRequestedException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof UnsupportedSparseFieldsetRequestedException) {
            $response = new BadRequestJsonApiResponse($exception->getMessage());
            $event->setResponse($response);

            return;
        }

        if ($exception instanceof UnsupportedMediaTypeException) {
            $response = new UnsupportedMediaTypeJsonApiResponse($exception);
            $event->setResponse($response);

            return;
        }
    }
}
