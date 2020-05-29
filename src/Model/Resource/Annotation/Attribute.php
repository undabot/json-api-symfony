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
    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var string */
    public $example;

    /** @var string */
    public $format;

    /** @var bool */
    public $nullable;
}
