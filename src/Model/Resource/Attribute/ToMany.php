<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Attribute;

use Attribute as PhpAttribute;

#[PhpAttribute(PhpAttribute::TARGET_PROPERTY | PhpAttribute::IS_REPEATABLE)]
class ToMany extends Relationship
{
    public function isToMany(): bool
    {
        return true;
    }
}
