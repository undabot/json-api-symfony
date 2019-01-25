<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Request;

use Undabot\JsonApi\Model\Resource\ResourceInterface;

class CreateResourceRequest
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
