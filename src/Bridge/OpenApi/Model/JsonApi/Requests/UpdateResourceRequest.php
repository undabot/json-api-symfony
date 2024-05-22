<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Requests;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Request;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\UpdateSchema;

class UpdateResourceRequest implements Request
{
    public function __construct(private string $resourceType, private UpdateSchema $schema) {}

    public function getContentType(): string
    {
        return 'application/vnd.api+json';
    }

    public function getSchemaReference(): string
    {
        return $this->schema->getName();
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }
}
