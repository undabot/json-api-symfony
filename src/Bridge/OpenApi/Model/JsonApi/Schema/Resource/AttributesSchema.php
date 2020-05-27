<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;

/**
 * Partial that represents `attributes` section of the JSON:API resource response.
 */
final class AttributesSchema implements Schema
{
    /** @var AttributeSchema[] */
    private $attributes;

    /**
     * @param AttributeSchema[] $attributes
     */
    public function __construct(array $attributes)
    {
        Assertion::allIsInstanceOf($attributes, AttributeSchema::class);
        $this->attributes = $attributes;
    }

    public function toOpenApi(): array
    {
        $attributes = [];

        /** @var AttributeSchema $attributeSchema */
        foreach ($this->attributes as $attributeSchema) {
            $attributes[$attributeSchema->getName()] = $attributeSchema->toOpenApi();
        }

        if (true === empty($attributes)) {
            return [];
        }

        return [
            'type' => 'object',
            'nullable' => false,
            'properties' => $attributes,
            'required' => array_keys($attributes),
        ];

        /*
         * @todo Should we support optional attributes?
         *
         * Resource attributes are always required, and should not be ommited from the create or update request payload.
         * In cases where the Create resource is different from the Read resource (e.g. generated properties such as timestamps
         * or slugs) we can create two separate api models: create and read.
         *
         * Example implementation:
         * ```php
         *   $requiredAttributes = array_keys(array_filter($attributeSchemas, function (AttributeSchema $attributeSchema) {
         *       return $attributeSchema->isRequired();
         *   }));
         *   if (false === empty($requiredAttributes)) {
         *       $openApi['required'] = $requiredAttributes;
         *   }
         * ```
         */
    }
}
