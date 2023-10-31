<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\ResourceSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\RelationshipSchema;

class CreateSchema implements ResourceSchema
{
    /** @var string */
    private $resourceType;

    /** @var AttributeSchema[] */
    private $attributes;

    /** @var RelationshipSchema[] */
    private $relationships;

    /**
     * @param AttributeSchema[]    $attributes
     * @param RelationshipSchema[] $relationships
     */
    public function __construct(string $resourceType, array $attributes, array $relationships)
    {
        Assertion::allIsInstanceOf($attributes, AttributeSchema::class);
        Assertion::allIsInstanceOf($relationships, RelationshipSchema::class);
        $this->resourceType = $resourceType;
        $this->attributes = $attributes;
        $this->relationships = $relationships;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getName(): string
    {
        return ucwords($this->resourceType) . 'CreateModel';
    }

    public function toOpenApi(): array
    {
        $required = ['type'];

        if (!empty($this->attributes)) {
            $required[] = 'attributes';
            $attributesSchema = new AttributesSchema($this->attributes);
            $schema['properties']['attributes'] = $attributesSchema->toOpenApi();
        }

        if (!empty($this->relationships)) {
            $required[] = 'relationships';
            $relationshipsSchema = new RelationshipsSchema($this->relationships);
            $schema['properties']['relationships'] = $relationshipsSchema->toOpenApi();
        }

        return [
            'type' => 'object',
            'properties' => [
                'type' => [
                    'type' => 'string',
                    'example' => $this->resourceType,
                    'enum' => [$this->resourceType],
                ],
            ],
            'required' => $required,
        ];
    }
}
