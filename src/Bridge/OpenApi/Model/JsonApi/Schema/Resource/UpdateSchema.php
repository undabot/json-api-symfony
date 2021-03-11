<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\ResourceSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\RelationshipSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\UuidSchema;

class UpdateSchema implements ResourceSchema
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
        $this->resourceType = $resourceType;
        $this->attributes = $attributes;
        $this->relationships = $relationships;
    }

    public function getName(): string
    {
        return ucwords($this->resourceType) . 'UpdateModel';
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function toOpenApi(): array
    {
        $required = [
            'type',
            'id',
        ];

        $schema = [
            'type' => 'object',
            'required' => $required,
            'properties' => [
                'id' => (new UuidSchema())->toOpenApi(),
                'type' => [
                    'type' => 'string',
                    'example' => $this->resourceType,
                    'enum' => [$this->resourceType],
                ],
            ],
        ];

        if (false === empty($this->attributes)) {
            $attributesSchema = (new AttributesSchema($this->attributes))->toOpenApi();
        }

        if (false === empty($this->relationships)) {
            $relationshipsSchema = (new RelationshipsSchema($this->relationships))->toOpenApi();
        }

        if (false === empty($attributesSchema)) {
            $schema['properties']['attributes'] = $attributesSchema;
        }

        if (false === empty($relationshipsSchema)) {
            $schema['properties']['relationships'] = $relationshipsSchema;
        }

        return $schema;
    }
}
