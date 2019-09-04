<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Request;

use Undabot\JsonApi\Model\Request\CreateResourceRequestInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

class CreateResourceRequest implements CreateResourceRequestInterface
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
