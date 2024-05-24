<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Request;

use Undabot\JsonApi\Definition\Model\Request\UpdateResourceRequestInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;

class UpdateResourceRequest implements UpdateResourceRequestInterface
{
    public function __construct(private ResourceInterface $resource) {}

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }
}
