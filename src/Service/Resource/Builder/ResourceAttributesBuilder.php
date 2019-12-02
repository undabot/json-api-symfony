<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Builder;

use Undabot\JsonApi\Implementation\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Implementation\Model\Resource\Attribute\AttributeCollection;

class ResourceAttributesBuilder
{
    /** @var array */
    private $attributes = [];

    public static function make(): self
    {
        return new self();
    }

    /**
     * @param mixed $value
     */
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
