<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Undabot\JsonApi\Definition\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Definition\Model\Meta\MetaInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;

final class ResourceResponse
{
    private ?ResourceInterface $primaryResource;

    private ?ResourceCollectionInterface $includedResources;

    private ?MetaInterface $meta;

    private ?LinkCollectionInterface $links;

    public function __construct(
        ?ResourceInterface $primaryResource,
        ?ResourceCollectionInterface $includedResources = null,
        ?MetaInterface $meta = null,
        ?LinkCollectionInterface $links = null
    ) {
        $this->primaryResource = $primaryResource;
        $this->includedResources = $includedResources;
        $this->meta = $meta;
        $this->links = $links;
    }

    public function getPrimaryResource(): ?ResourceInterface
    {
        return $this->primaryResource;
    }

    public function getIncludedResources(): ?ResourceCollectionInterface
    {
        return $this->includedResources;
    }

    public function getMeta(): ?MetaInterface
    {
        return $this->meta;
    }

    public function getLinks(): ?LinkCollectionInterface
    {
        return $this->links;
    }
}
