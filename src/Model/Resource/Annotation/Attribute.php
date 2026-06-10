<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Annotation;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Attribute
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public mixed $example = null,
        public ?string $format = null,
        public ?bool $nullable = null,
    ) {}
}
