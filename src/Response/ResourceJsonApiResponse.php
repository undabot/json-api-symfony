<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class ResourceJsonApiResponse extends Response implements JsonApiResponseInterface
{
    /** @var ResourceInterface */
    private $jsonApiResource;

    /** @var ResourceCollection|null */
    private $includedResources;

    public function __construct(ResourceInterface $resource, ?ResourceCollection $includedResources, array $headers =
    [])
    {
        parent::__construct(null, Response::HTTP_OK, $headers);
        $this->jsonApiResource = $resource;
        $this->includedResources = $includedResources;
    }

    public function getJsonApiResource(): ResourceInterface
    {
        return $this->jsonApiResource;
    }

    public function getIncludedResources(): ?ResourceCollection
    {
        return $this->includedResources;
    }
}
