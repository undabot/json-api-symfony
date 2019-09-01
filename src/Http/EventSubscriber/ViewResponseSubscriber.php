<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Undabot\JsonApi\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\JsonApi\Model\Document\Document;
use Undabot\JsonApi\Model\Document\DocumentData;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCreatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceUpdatedResponse;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceValidationErrorsResponse;

class ViewResponseSubscriber implements EventSubscriberInterface
{
    /** @var DocumentToPhpArrayEncoderInterface */
    private $documentEncoder;

    public function __construct(DocumentToPhpArrayEncoderInterface $documentEncoder)
    {
        $this->documentEncoder = $documentEncoder;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => 'buildView',
        ];
    }

    public function buildView(ViewEvent $event)
    {
        $data = $event->getControllerResult();

        if ($data instanceof ResourceCollectionResponse) {
            $document = new Document(
                new DocumentData($data->getPrimaryResources()),
                null,
                $data->getMeta(),
                $this->buildJsonApi(),
                $data->getLinks(),
                $data->getIncludedResources()
            );

            $response = $this->buildDocumentResponse($document);
            $event->setResponse($response);
        }

        if ($data instanceof ResourceCreatedResponse) {
            $document = new Document(
                new DocumentData($data->getPrimaryResource()),
                null,
                $data->getMeta(),
                $this->buildJsonApi(),
                $data->getLinks()
            );

            $response = $this->buildDocumentResponse($document, Response::HTTP_CREATED);
            $event->setResponse($response);
        }

        if ($data instanceof ResourceUpdatedResponse) {
            $document = new Document(
                new DocumentData($data->getPrimaryResource()),
                null,
                $data->getMeta(),
                $this->buildJsonApi(),
                $data->getLinks()
            );

            $response = $this->buildDocumentResponse($document, Response::HTTP_OK);
            $event->setResponse($response);
        }

        if ($data instanceof ResourceResponse) {
            $document = new Document(
                new DocumentData($data->getPrimaryResource()),
                null,
                $data->getMeta(),
                $this->buildJsonApi(),
                $data->getLinks(),
                $data->getIncludedResources()
            );

            $response = $this->buildDocumentResponse($document);
            $event->setResponse($response);
        }

        if ($data instanceof ResourceValidationErrorsResponse) {
            $document = new Document(null, $data->getErrorCollection());
            $response = $this->buildDocumentResponse($document);
            $event->setResponse($response);

            return;
        }
    }

    private function buildJsonApi(): MetaInterface
    {
        return new Meta([
            'version' => '1.0',
        ]);
    }

    private function buildDocumentResponse(Document $document, int $status = Response::HTTP_OK): Response
    {
        $content = $this->documentEncoder->encode($document);

        return new Response(
            json_encode($content),
            $status,
            [
                'Content-Type' => 'application/vnd.api+json',
            ]
        );
    }
}