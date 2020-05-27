<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

interface Response
{
    public function getStatusCode(): int;

    public function getContentType(): string;

    public function getDescription(): ?string;

    /** @return mixed[] */
    public function toOpenApi(): array;
}
