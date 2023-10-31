<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Attribute
{
    /** @var string|null */
    public ?string $name;

    /** @var string */
    public string $description;

    /** @var string */
    public string $example;

    /** @var string */
    public string $format;

    /** @var bool */
    public bool $nullable;
}
