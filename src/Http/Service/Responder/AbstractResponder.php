<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Exception;
use Undabot\JsonApi\Model\Link\Link;
use Undabot\JsonApi\Model\Link\LinkCollection;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Resource\ResourceCollection;
use Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\MappedModelEncoder;

abstract class AbstractResponder
{
    /** @var MappedModelEncoder */
    protected $encoder;

    public function __construct(MappedModelEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    protected function buildLinks(?array $links): ?LinkCollection
    {
        if (null !== $links) {
            $constructedLinks = [];
            foreach ($links as $linkName => $linkValue) {
                $constructedLinks[] = new Link($linkName, $linkValue);
            }

            return new LinkCollection($constructedLinks);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    protected function buildIncluded(?array $models): ?ResourceCollection
    {
        if (null !== $models) {
            $includedResources = $this->encoder->encodeModels($models);

            return new ResourceCollection($includedResources);
        }

        return null;
    }

    protected function buildMeta(?array $meta): ?Meta
    {
        if (null !== $meta) {
            return new Meta($meta);
        }

        return null;
    }
}
