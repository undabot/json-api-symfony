<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response\Factory;

use Undabot\JsonApi\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\JsonApi\Model\Document\Document;
use Undabot\JsonApi\Model\Document\DocumentData;
use Undabot\JsonApi\Model\Document\DocumentDataInterface;
use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;
use Undabot\JsonApi\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;

class JsonApiHttpResponseFactory
{
    /** @var DocumentToPhpArrayEncoderInterface */
    private $documentEncoder;

    public function __construct(DocumentToPhpArrayEncoderInterface $documentEncoder)
    {
        $this->documentEncoder = $documentEncoder;
    }

    private function makeDocument(
        DocumentDataInterface $documentData,
        ?MetaInterface $meta = null,
        ?ErrorCollectionInterface $errors = null,
        ?LinkCollectionInterface $links = null,
        ResourceCollectionInterface $included = null
    ): Document {
        $jsonApiMeta = null;

        return new Document($documentData, $errors, $meta, $jsonApiMeta, $links, $included);
    }

    public function makeSuccess($documentData, ?array $meta = null)
    {
        $documentData = new DocumentData($documentData);
        if (null !== $meta) {
            $meta = new Meta($meta);
        }

        $document = $this->makeDocument($documentData, $meta);
    }
}
