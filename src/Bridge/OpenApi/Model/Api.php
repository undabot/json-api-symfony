<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Endpoint;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\ResourceSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Server;

class Api implements Contract\Api
{
    /** @var string */
    private $title;

    /** @var string */
    private $version;

    /** @var string */
    private $description;

    /** @var null|string */
    private $email;

    /** @var Endpoint[] */
    private $endpoints = [];

    /** @var Server[] */
    private $servers = [];

    /** @var mixed[] */
    private $schemas = [];

    /** @todo add support for security schemas */
    public function __construct(
        string $title,
        string $version,
        string $description,
        ?string $email = null
    ) {
        $this->title = $title;
        $this->version = $version;
        $this->description = $description;
        $this->email = $email;
    }

    public function addEndpoint(Endpoint $endpoint): void
    {
        $this->endpoints[] = $endpoint;
    }

    public function addSchema(ResourceSchema $schema): void
    {
        $this->schemas[$schema->getName()] = $schema->toOpenApi();
    }

    /**
     * @param ResourceSchema[] $includedSchemas
     */
    public function addSchemas(array $includedSchemas): void
    {
        Assertion::allIsInstanceOf($includedSchemas, ResourceSchema::class);
        foreach ($includedSchemas as $includedSchema) {
            $this->addSchema($includedSchema);
        }
    }

    public function addServer(Server $server): void
    {
        $this->servers[] = $server;
    }

    public function toOpenApi(): array
    {
        $api = [
            'openapi' => '3.0.0',
            // @todo contact
            // @todo license
            'info' => [
                'description' => $this->description,
                'version' => $this->version,
                'title' => $this->title,
            ],
            'paths' => [],
        ];

        /** @var Server $server */
        foreach ($this->servers as $server) {
            $api['servers'][] = $server->toOpenApi();
        }

        /** @var Endpoint $endpoint */
        foreach ($this->endpoints as $endpoint) {
            $api['paths'][$endpoint->getPath()][$endpoint->getMethod()] = $endpoint->toOpenApi();
        }

        foreach ($this->schemas as $name => $schema) {
            $api['components']['schemas'][$name] = $schema;
        }

        return $api;
    }
}
