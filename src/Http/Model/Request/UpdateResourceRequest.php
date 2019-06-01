<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Request;

use Undabot\JsonApi\Model\Request\UpdateResourceRequestInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class UpdateResourceRequest implements UpdateResourceRequestInterface
{
    /** @var ResourceInterface */
    private $resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }
}
