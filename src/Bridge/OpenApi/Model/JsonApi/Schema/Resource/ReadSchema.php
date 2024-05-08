<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\ResourceSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\RelationshipSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\StringSchema;

class ReadSchema implements ResourceSchema
{
    /** @var AttributeSchema[] */
    private array $attributes;

    /** @var RelationshipSchema[] */
    private array $relationships;

    /**
     * @param AttributeSchema[]    $attributes
     * @param RelationshipSchema[] $relationships
     */
    public function __construct(
        private string $resourceType,
        array $attributes,
        array $relationships
    ) {
        Assertion::allIsInstanceOf($attributes, AttributeSchema::class);
        Assertion::allIsInstanceOf($relationships, RelationshipSchema::class);
        $this->attributes = $attributes;
        $this->relationships = $relationships;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getName(): string
    {
        return ucwords($this->resourceType) . 'ReadModel';
    }

    public function toOpenApi(): array
    {
        $required = [
            'id',
            'type',
        ];

        if (false === empty($this->attributes)) {
            $required[] = 'attributes';
        }

        if (false === empty($this->relationships)) {
            $required[] = 'relationships';
        }

        $schema = [
            'type' => 'object',
            'required' => $required,
            'properties' => [
                'id' => (new StringSchema())->toOpenApi(),
                'type' => [
                    'type' => 'string',
                    'example' => $this->resourceType,
                    'enum' => [$this->resourceType],
                ],
            ],
        ];

        if (false === empty($this->attributes)) {
            $attributesSchema = new AttributesSchema($this->attributes);
            $schema['properties']['attributes'] = $attributesSchema->toOpenApi();
        }

        if (false === empty($this->relationships)) {
            $relationshipsSchema = new RelationshipsSchema($this->relationships);
            $schema['properties']['relationships'] = $relationshipsSchema->toOpenApi();
        }

        return $schema;
    }
}
