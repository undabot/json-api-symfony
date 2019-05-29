<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
abstract class Relationship
{
    /** @var string */
    public $name;

    /** @var string */
    public $type;

    /** @var string */
    public $description;

    public abstract function isToMany(): bool;
}
