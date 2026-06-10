<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Annotation;

abstract class Relationship
{
    public function __construct(
        public ?string $name = null,
        public ?string $type = null,
        public ?string $description = null,
        public ?bool $nullable = null,
    ) {}

    abstract public function isToMany(): bool;
}
