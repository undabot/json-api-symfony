<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Undabot\JsonApi\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

final class ResourceUpdatedResponse
{
    /** @var ResourceInterface */
    private $primaryResource;

    /** @var MetaInterface|null */
    private $meta;

    /** @var LinkCollectionInterface|null */
    private $links;

    public function __construct(
        ResourceInterface $primaryResource,
        ?ResourceCollectionInterface $includedResources = null,
        ?MetaInterface $meta = null,
        ?LinkCollectionInterface $links = null
    ) {
        $this->primaryResource = $primaryResource;
        $this->meta = $meta;
        $this->links = $links;
    }

    public function getPrimaryResource(): ResourceInterface
    {
        return $this->primaryResource;
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
