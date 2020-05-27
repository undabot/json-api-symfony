<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

class Server implements Contract\Server
{
    /** @var string */
    private $url;

    /** @var null|string */
    private $description;

    public function __construct(string $url, ?string $description = null)
    {
        $this->url = $url;
        $this->description = $description;
    }

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
