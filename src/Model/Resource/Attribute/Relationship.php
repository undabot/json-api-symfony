<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Attribute;

abstract class Relationship
{
    public function __construct(
        public string $name,
        public ?string $type = null,
        public ?string $description = null,
        public bool $nullable = false
    ) {}

    abstract public function isToMany(): bool;
}
