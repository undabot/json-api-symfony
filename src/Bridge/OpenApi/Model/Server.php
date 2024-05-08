<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

class Server implements Contract\Server
{
    public function __construct(private string $url, private ?string $description = null) {}

    public function toOpenApi(): array
    {
        $schema = [
            'url' => $this->url,
        ];

        if (null !== $this->description) {
            $schema['description'] = $this->description;
        }

        return $schema;
    }
}
