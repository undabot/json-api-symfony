<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Requests;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Request;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\ResourceSchema;

class CreateResourceRequest implements Request
{
    public function __construct(private readonly string $resourceType, private readonly ResourceSchema $schema) {}

    public function getContentType(): string
    {
        return 'application/vnd.api+json';
    }

    public function getSchemaReference(): string
    {
        return $this->schema->getName();
    }
}
