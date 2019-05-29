<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\EventListener;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Undabot\JsonApi\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\JsonApi\Model\Document\Document;
use Undabot\JsonApi\Model\Document\DocumentData;
use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Response\AbstractErrorJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\JsonApiErrorResponseInterface;
use Undabot\SymfonyJsonApi\Response\JsonApiResponseInterface;
use Undabot\SymfonyJsonApi\Response\NotFoundJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\ResourceCollectionJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\ResourceCreatedJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\ResourceJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\ResourceUpdatedJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\ValidationErrorsJsonApiResponse;

class JsonApiResponseEncoderListener implements EventSubscriberInterface
{
    /** @var DocumentToPhpArrayEncoderInterface */
    private $documentToPhpArrayEncoderInterface;

    public function __construct(DocumentToPhpArrayEncoderInterface $documentToPhpArrayEncoderInterface)
    {
        $this->documentToPhpArrayEncoderInterface = $documentToPhpArrayEncoderInterface;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => 'encodeJsonApiPayload',
        ];
    }

    public function encodeJsonApiPayload(FilterResponseEvent $event): void
    {
        $response = $event->getResponse();

        if (false === ($response instanceof JsonApiResponseInterface)) {
            return;
        }

        $response->headers->set('Content-Type', 'application/vnd.api+json');

        if (true === ($response instanceof JsonApiErrorResponseInterface)) {
            $this->encodeErrorResponseContent($response);
        }

        if (true === ($response instanceof AbstractErrorJsonApiResponse)) {
            $this->encodeErrorResponseContent($response);
        }

        if (true === ($response instanceof ResourceJsonApiResponse)) {
            $this->encodeResource($response);
        }

        if (true === ($response instanceof ResourceCreatedJsonApiResponse)) {
            $this->encodeResourceCreated($response);
        }

        if (true === ($response instanceof ResourceUpdatedJsonApiResponse)) {
            $this->encodeResourceUpdated($response);
        }

        if (true === ($response instanceof ResourceCollectionJsonApiResponse)) {
            $this->encodeResourceCollection($response);
        }

        if (true === ($response instanceof ValidationErrorsJsonApiResponse)) {
            $this->encodeValidationErrors($response);
        }

        if (true === ($response instanceof NotFoundJsonApiResponse)) {
            $this->encodeErrorResponseContent($response);
        }
    }

    private function encodeErrorResponseContent(AbstractErrorJsonApiResponse $response): void
    {
        $document = new Document(null, $response->getErrorCollection());
        $encodedContent = $this->documentToPhpArrayEncoderInterface->encode($document);
        $response->setContent(json_encode($encodedContent));
    }

    private function encodeResource(ResourceJsonApiResponse $response): void
    {
        $documentData = new DocumentData($response->getJsonApiResource());

        $document = new Document(
            $documentData,
            null,
            null,
            null,
            null,
            $response->getIncludedResources()
        );

        $content = $this->documentToPhpArrayEncoderInterface->encode($document);
        $response->setContent(json_encode($content));
    }

    private function encodeResourceCreated(ResourceCreatedJsonApiResponse $response): void
    {
        $document = $this->createResourceDocument($response->getJsonApiResource());
        $content = $this->documentToPhpArrayEncoderInterface->encode($document);
        $response->setContent(json_encode($content));
    }

    private function createResourceDocument(ResourceInterface $resource)
    {
        $documentData = new DocumentData($resource);

        return new Document($documentData);
    }

    private function encodeResourceUpdated(ResourceUpdatedJsonApiResponse $response): void
    {
        $document = $this->createResourceDocument($response->getJsonApiResource());
        $content = $this->documentToPhpArrayEncoderInterface->encode($document);
        $response->setContent(json_encode($content));
    }

    private function encodeResourceCollection(ResourceCollectionJsonApiResponse $response): void
    {
        $metaData = [];
        if (null !== $response->getTotalCount()) {
            $metaData['total'] = $response->getTotalCount();
        }
        $meta = $this->createMeta($metaData);

        $documentData = new DocumentData($response->getJsonApiResourceCollection());
        $document = new Document(
            $documentData,
            null,
            $meta,
            null,
            null,
            $response->getIncludedResourcesCollection()
        );

        $content = $this->documentToPhpArrayEncoderInterface->encode($document);
        $response->setContent(json_encode($content));
    }

    private function createMeta(array $metaData): ?MetaInterface
    {
        if (true === empty($metaData)) {
            return null;
        }

        return new Meta($metaData);
    }

    private function encodeValidationErrors(ValidationErrorsJsonApiResponse $response): void
    {
        if (null === $response->getErrorCollection()) {
            throw new Exception('Couldn\'t encode response without error collection');
        }

        $document = $this->createErrorDocument($response->getErrorCollection());
        $content = $this->documentToPhpArrayEncoderInterface->encode($document);
        $response->setContent(json_encode($content));
    }

    private function createErrorDocument(ErrorCollectionInterface $errorCollection): Document
    {
        $document = new Document(null, $errorCollection);

        return $document;
    }
}
