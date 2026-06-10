<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Annotation;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ToOne extends Relationship
{
    public function isToMany(): bool
    {
        return false;
    }
}
