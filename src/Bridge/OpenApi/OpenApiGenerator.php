<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\Api;

final class OpenApiGenerator
{
    /** @var array<OpenApiDefinition> */
    private array $definitions = [];

    /** @var array<string,array<ResourceApiInterface>> */
    private array $resources = [];

    /** @psalm-suppress PossiblyUnusedMethod */
    public function addDefinition(OpenApiDefinition $definition): void
    {
        $this->definitions[\get_class($definition)] = $definition;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function addResource(ResourceApiInterface $resource): void
    {
        $this->resources[$resource->getDefinition()][] = $resource;
    }

    /**
     * @return array<OpenApiDefinition>
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function generateApi(OpenApiDefinition $definition): Api
    {
        $api = $definition->getApi();
        if (isset($this->resources[\get_class($definition)])) {
            foreach ($this->resources[\get_class($definition)] as $resource) {
                $resourceApiEndpoint = $resource->generateResourceApiEndpoints();
                $resourceApiEndpoint->addToApi($api);
            }
        }

        return $api;
    }
}
