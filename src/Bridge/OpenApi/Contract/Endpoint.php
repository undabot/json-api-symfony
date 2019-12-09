<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract;

interface Endpoint extends Schema
{
    public const METHOD_GET = 'get';
    public const METHOD_POST = 'post';
    public const METHOD_PUT = 'put';
    public const METHOD_PATCH = 'patch';

    public function getMethod(): string;

    public function getPath(): string;

    public function getResponses(): array;

    public function getParams(): ?array;
}
