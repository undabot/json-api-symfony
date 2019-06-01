<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\HttpFoundation\Response;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;

class ResourceCollectionJsonApiResponse extends Response implements JsonApiResponseInterface
{
    /** @var ResourceCollectionInterface */
    private $jsonApiResourceCollection;

    /** @var int|null */
    private $totalCount;

    /** @var ResourceCollectionInterface|null */
    private $includedResourcesCollection;

    public function __construct(
        ResourceCollectionInterface $jsonApiResourceCollection,
        ?int $totalCount = null,
        ?ResourceCollectionInterface $includedResourcesCollection = null,
        array $headers = []
    ) {
        parent::__construct(null, Response::HTTP_OK, $headers);
        $this->jsonApiResourceCollection = $jsonApiResourceCollection;
        $this->totalCount = $totalCount;
        $this->includedResourcesCollection = $includedResourcesCollection;
    }

    public function getJsonApiResourceCollection(): ResourceCollectionInterface
    {
        return $this->jsonApiResourceCollection;
    }

    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    public function getIncludedResourcesCollection(): ?ResourceCollectionInterface
    {
        return $this->includedResourcesCollection;
    }
}
