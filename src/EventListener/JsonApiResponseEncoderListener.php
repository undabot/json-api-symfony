<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\EventListener;

use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Undabot\JsonApi\Encoding\PhpArray\Encode\DocumentPhpArrayEncoderInterface;
use Undabot\JsonApi\Model\Document\Document;
use Undabot\JsonApi\Model\Document\DocumentData;
use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Response\AbstractErrorJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\JsonApiResponseInterface;
use Undabot\SymfonyJsonApi\Response\ResourceCollectionJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\ResourceCreatedJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\ResourceJsonApiResponse;
use Undabot\SymfonyJsonApi\Response\ValidationErrorsJsonApiResponse;

class JsonApiResponseEncoderListener implements EventSubscriberInterface
{
    /** @var DocumentPhpArrayEncoderInterface */
    private $documentEncoder;

    public function __construct(DocumentPhpArrayEncoderInterface $documentEncoder)
    {
        $this->documentEncoder = $documentEncoder;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.response' => 'encodeJsonApiPayload',
        ];
    }

    public function encodeJsonApiPayload(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        if (false === ($response instanceof JsonApiResponseInterface)) {
            return;
        }

        $response->headers->set('Content-Type', 'application/vnd.api+json');

        if (true === ($response instanceof AbstractErrorJsonApiResponse)) {
            $this->encodeErrorResponse($response);
        }

        if (true === ($response instanceof ResourceJsonApiResponse)) {
            $this->encodeResource($response);
        }

        if (true === ($response instanceof ResourceCreatedJsonApiResponse)) {
            $this->encodeResourceCreated($response);
        }

        if (true === ($response instanceof ResourceCollectionJsonApiResponse)) {
            $this->encodeResourceCollection($response);
        }

        if (true === ($response instanceof ValidationErrorsJsonApiResponse)) {
            $this->encodeValidationErrors($response);
        }
    }

    private function createErrorDocument(ErrorCollectionInterface $errorCollection)
    {
        $document = new Document(null, $errorCollection);

        return $document;
    }

    private function createResourceDocument(ResourceInterface $resource)
    {
        $documentData = new DocumentData($resource);
        $document = new Document($documentData);

        return $document;
    }

    private function encodeResource(ResourceJsonApiResponse $response)
    {
        $document = $this->createResourceDocument($response->getJsonApiResource());
        $content = $this->documentEncoder->encode($document);
        $response->setContent(json_encode($content));
    }

    private function encodeResourceCreated(ResourceCreatedJsonApiResponse $response)
    {
        $document = $this->createResourceDocument($response->getJsonApiResource());
        $content = $this->documentEncoder->encode($document);
        $response->setContent(json_encode($content));
    }

    private function createMeta(array $metaData): ?MetaInterface
    {
        if (false === empty($metaData)) {
            return new Meta($metaData);
        }

        return null;
    }

    private function encodeResourceCollection(ResourceCollectionJsonApiResponse $response)
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

        $content = $this->documentEncoder->encode($document);
        $response->setContent(json_encode($content));
    }

    private function encodeValidationErrors(ValidationErrorsJsonApiResponse $response): void
    {
        if (null === $response->getErrorCollection()) {
            throw new Exception('Couldn\'t encode response without error collection');
        }

        $document = $this->createErrorDocument($response->getErrorCollection());
        $content = $this->documentEncoder->encode($document);
        $response->setContent(json_encode($content));
    }

    private function encodeErrorResponse(AbstractErrorJsonApiResponse $errorResponse)
    {
        if (null === $errorResponse->getErrorCollection()) {
            throw new Exception('Couldn\'t encode response without error collection');
        }

        $document = $this->createErrorDocument($errorResponse->getErrorCollection());
        $content = $this->documentEncoder->encode($document);
        $errorResponse->setContent(json_encode($content));
    }
}
