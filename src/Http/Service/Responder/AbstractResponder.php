<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Undabot\JsonApi\Model\Link\Link;
use Undabot\JsonApi\Model\Link\LinkCollection;
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

    protected function buildLinks(array $links): LinkCollection
    {
        $constructedLinks = [];
        foreach ($links as $linkName => $linkValue) {
            $constructedLinks[] = new Link($linkName, $linkValue);
        }

        return new LinkCollection($constructedLinks);
    }

    protected function buildIncluded(array $models): ResourceCollection
    {
        $includedResources = $this->encoder->encodeModels($models);

        return new ResourceCollection($includedResources);
    }
}
