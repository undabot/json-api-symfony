<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class ToMany extends Relationship
{
    public function isToMany(): bool
    {
        return true;
    }
}
