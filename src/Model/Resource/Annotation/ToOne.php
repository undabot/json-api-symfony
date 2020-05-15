<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class ToOne extends Relationship
{
    public function isToMany(): bool
    {
        return false;
    }
}
