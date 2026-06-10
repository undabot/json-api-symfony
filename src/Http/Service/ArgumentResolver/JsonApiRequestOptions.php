<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ArgumentResolver;

/**
 * Configures how a JSON:API request controller argument is resolved.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class JsonApiRequestOptions
{
    /**
     * @param bool   $clientGeneratedIds whether the client is allowed to provide the resource ID when creating a resource
     * @param string $idAttribute        name of the route attribute (URL path param) that contains the resource ID
     */
    public function __construct(
        public bool $clientGeneratedIds = false,
        public string $idAttribute = 'id',
    ) {}
}
