<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

interface Api extends Schema
{
    public function addEndpoint(Endpoint $endpoint);

    public function addSchema(ResourceSchema $schema);

    /**
     * @param ResourceSchema[] $includedSchemas
     */
    public function addSchemas(array $includedSchemas);

    public function addServer(Server $server);
}
