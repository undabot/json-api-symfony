<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation;

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
}
