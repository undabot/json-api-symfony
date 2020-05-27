<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\Api;

final class ApiTransformer
{
    public function toJson(Api $api): string
    {
        return json_encode(
            $api->toOpenApi(),
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}
