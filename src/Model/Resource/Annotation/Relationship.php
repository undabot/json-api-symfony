<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
abstract class Relationship
{
    /** @var string|null */
    public ?string $name;

    /** @var string|null */
    public ?string $type;

    /** @var string */
    public string $description;

    /** @var bool */
    public bool $nullable;

    abstract public function isToMany(): bool;
}
