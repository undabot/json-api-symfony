<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

interface Api extends Schema
{
    public function addEndpoint(Endpoint $endpoint): void;

    public function addSchema(ResourceSchema $schema): void;

    /**
     * @param ResourceSchema[] $includedSchemas
     */
    public function addSchemas(array $includedSchemas): void;

    public function addServer(Server $server): void;
}
