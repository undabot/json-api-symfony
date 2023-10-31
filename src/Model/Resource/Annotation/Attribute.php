<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 *
 * @Target("PROPERTY")
 */
class Attribute
{
    public ?string $name;

    public string $description;

    public string $example;

    public string $format;

    public bool $nullable;
}
