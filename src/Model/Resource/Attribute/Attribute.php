<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Attribute;

use Attribute as PhpAttribute;

#[PhpAttribute(PhpAttribute::TARGET_PROPERTY | PhpAttribute::IS_REPEATABLE)]
class Attribute
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $example = null,
        public ?string $format = null,
        public bool $nullable = false
    ) {}
}
