<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Assert\Assertion;
use Undabot\JsonApi\Definition\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Definition\Model\Link\LinkInterface;
use Undabot\JsonApi\Definition\Model\Meta\MetaInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\JsonApi\Implementation\Model\Link\LinkCollection;
use Undabot\JsonApi\Implementation\Model\Meta\Meta;
use Undabot\JsonApi\Implementation\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Model\Collection\ObjectCollection;

final class ResourceCollectionResponse
{
    /** @var ResourceCollectionInterface */
    private $primaryResources;

    /** @var null|ResourceCollectionInterface */
    private $includedResources;

    /** @var null|MetaInterface */
    private $meta;

    /** @var null|LinkCollectionInterface */
    private $links;

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

    public static function fromObjectCollection(
        ObjectCollection $primaryResources,
        ?ResourceCollectionInterface $includedResources = null,
        ?MetaInterface $meta = null,
        ?LinkCollectionInterface $links = null
    ): self {
        if (null === $meta) {
            $meta = new Meta(['total' => $primaryResources->count()]);
        }

        return new self(
            new ResourceCollection($primaryResources->getItems()),
            $includedResources,
            $meta,
            $links
        );
    }

    /**
     * @param ResourceInterface[]      $resources
     * @param null|ResourceInterface[] $included
     * @param null|LinkInterface[]     $links
     */
    public static function fromArray(
        array $resources,
        ?array $included = null,
        ?array $meta = null,
        ?array $links = null
    ): self {
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
        if (null !== $links) {
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
