<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Undabot\JsonApi\Encoding\DocumentToPhpArrayEncoderInterface;

class JsonApiRequestListener implements EventSubscriberInterface
{
    /** @var DocumentToPhpArrayEncoderInterface */
    private $documentEncoder;

    public function __construct(DocumentToPhpArrayEncoderInterface $documentEncoder)
    {
        $this->documentEncoder = $documentEncoder;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onRequest',
            'kernel.controller' => 'beforeController',
        ];
    }

    public function onRequest(GetResponseEvent $event)
    {
        return;
        // @todo I don't think we should rely on middleware for these validations
        // @todo This should be implemented in the JsonApiRequestFactory and custom exception should be thrown
        // @todo while `kernel.exception` listener should pick them up and convert to proper response code.

        /*
         * Servers MUST respond with a 415 Unsupported Media Type status code if a request specifies the header
         * Content-Type: application/vnd.api+json with any media type parameters.
         */
        $request = $event->getRequest();
        if ('application/vnd.api+json' !== $request->headers->get('Content-Type')) {
            $event->setResponse(new Response(null, Response::HTTP_UNSUPPORTED_MEDIA_TYPE));
        }

        /*
         * Servers MUST respond with a 406 Not Acceptable status code if a request’s Accept header contains the
         * JSON:API media type and all instances of that media type are modified with media type parameters.
         */
        if (true === $request->headers->has('Accept')) {
            /** @var string $accepts */
            $accepts = $request->headers->get('Accept');
            $accepts = explode(',', $accepts);

            if (false === in_array('application/vnd.api+json', $accepts)) {
                $event->setResponse(new Response(null, Response::HTTP_NOT_ACCEPTABLE));
            }
        }
    }
}
