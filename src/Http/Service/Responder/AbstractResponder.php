<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Responder;

use Assert\Assertion;
use Undabot\JsonApi\Model\Link\Link;
use Undabot\JsonApi\Model\Link\LinkCollection;
use Undabot\JsonApi\Model\Link\LinkMemberInterface;
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

    /**
     * @param array<string, LinkMemberInterface> $links
     */
    protected function buildLinks(array $links): LinkCollection
    {
        Assertion::allIsInstanceOf($links, LinkMemberInterface::class);
        $constructedLinks = [];
        foreach ($links as $linkName => $linkValue) {
            $constructedLinks[] = new Link($linkName, $linkValue);
        }

        return new LinkCollection($constructedLinks);
    }

    /**
     * @param mixed[] $dataSet
     * @throws \Exception
     */
    protected function buildIncluded(array $dataSet): ResourceCollection
    {
        $includedResources = $this->encoder->encodeDataset($dataSet);

        return new ResourceCollection($includedResources);
    }
}
