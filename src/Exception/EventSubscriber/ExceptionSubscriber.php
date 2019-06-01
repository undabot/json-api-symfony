<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Exception\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Undabot\SymfonyJsonApi\Http\Exception\Request\ClientGeneratedIdIsNotAllowedException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestAcceptHeaderException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestContentTypeHeaderException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\InvalidRequestDataException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedFilterAttributeGivenException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedIncludeValuesGivenException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedMediaTypeException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedPaginationRequestedException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedQueryStringParameterGivenException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedSortRequestedException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedSparseFieldsetRequestedException;
use Undabot\SymfonyJsonApi\Http\Model\Response\BadRequestJsonApiResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\NotAcceptableJsonApiResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\UnsupportedMediaTypeJsonApiResponse;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'buildErrorResponse',
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
