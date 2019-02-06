<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class ResourceJsonApiResponse extends Response implements JsonApiResponseInterface
{
    /** @var ResourceInterface */
    private $jsonApiResource;

    /** @var ResourceCollectionInterface|null */
    private $includedResources;

    public function __construct(
        ResourceInterface $resource,
        ?ResourceCollectionInterface $includedResources = null,
        array $headers = []
    ) {
        parent::__construct(null, Response::HTTP_OK, $headers);
        $this->jsonApiResource = $resource;
        $this->includedResources = $includedResources;
    }

    public function getJsonApiResource(): ResourceInterface
    {
        return $this->jsonApiResource;
    }

    public function getIncludedResources(): ?ResourceCollectionInterface
    {
        return $this->includedResources;
    }
}
