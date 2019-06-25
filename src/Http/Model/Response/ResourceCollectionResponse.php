<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Assert\Assertion;
use Undabot\JsonApi\Model\Link\LinkCollection;
use Undabot\JsonApi\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Model\Link\LinkInterface;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollectionInterface;

final class ResourceCollectionResponse
{
    /** @var ResourceCollectionInterface */
    private $primaryResources;

    /** @var ResourceCollectionInterface|null */
    private $includedResources;

    /** @var MetaInterface|null */
    private $meta;

    /** @var LinkCollectionInterface|null */
    private $links;

    public static function fromObjectCollection(
        ObjectCollectionInterface $primaryResources,
        ?ResourceCollectionInterface $includedResources = null,
        ?MetaInterface $meta = null,
        ?LinkCollectionInterface $links = null
    ) {
        if ($meta === null) {
            $meta = new Meta(['total' => $primaryResources->count()]);
        }

        return new self(
            new ResourceCollection($primaryResources->getItems()),
            $includedResources,
            $meta,
            $links
        );
    }

    public function __construct(
        ResourceCollectionInterface $primaryResources,
        ?ResourceCollectionInterface $includedResources = null,
        ?MetaInterface $meta = null,
        ?LinkCollectionInterface $links = null
    ) {
        $this->primaryResources = $primaryResources;
        $this->includedResources = $includedResources;
        $this->meta = $meta;
        $this->links = $links;
    }

    public static function fromArray(
        array $resources,
        ?array $included = null,
        ?array $meta = null,
        ?array $links = null
    ) {
        Assertion::allIsInstanceOf($resources, ResourceInterface::class);

        $includedResources = null;
        if (null !== $included) {
            Assertion::allIsInstanceOf($included, ResourceInterface::class);
            $includedResources = new ResourceCollection($included);
        }

        if (null !== $meta) {
            $meta = new Meta($meta);
        }

        $linkCollection = null;
        if (null !== $linkCollection) {
            Assertion::allIsInstanceOf($links, LinkInterface::class);
            $linkCollection = new LinkCollection($links);
        }

        return new self(
            new ResourceCollection($resources),
            $includedResources,
            $meta,
            $linkCollection
        );
    }

    public function getPrimaryResources(): ResourceCollectionInterface
    {
        return $this->primaryResources;
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
