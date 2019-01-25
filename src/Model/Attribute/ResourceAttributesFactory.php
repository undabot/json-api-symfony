<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Attribute;

use Undabot\JsonApi\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollection;

class ResourceAttributesFactory
{
    /** @var array */
    private $attributes = [];

    public static function make()
    {
        return new self();
    }

    public function add(string $attributeName, $value): self
    {
        $this->attributes[$attributeName] = new Attribute($attributeName, $value);

        return $this;
    }

    public function get(): AttributeCollection
    {
        return new AttributeCollection($this->attributes);
    }
}
