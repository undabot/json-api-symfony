<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 *
 * @Target("PROPERTY")
 */
abstract class Relationship
{
    public ?string $name;

    public ?string $type;

    public string $description;

    public bool $nullable;

    abstract public function isToMany(): bool;
}
