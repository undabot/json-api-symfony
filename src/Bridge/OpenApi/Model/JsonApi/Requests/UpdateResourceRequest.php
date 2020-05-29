<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Requests;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Request;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\UpdateSchema;

class UpdateResourceRequest implements Request
{
    /** @var string */
    private $resourceType;

    /** @var UpdateSchema */
    private $schema;

    public function __construct(string $resourceType, UpdateSchema $schema)
    {
        $this->resourceType = $resourceType;
        $this->schema = $schema;
    }

    public function getContentType(): string
    {
        return 'application/vnd.api+json';
    }

    public function getSchemaReference(): string
    {
        return $this->schema->getName();
    }
}
