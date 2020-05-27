<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource;

use Assert\Assertion;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\RelationshipSchema;

/**
 * Partial that represents `relationships` section of the JSON:API resource response.
 */
final class RelationshipsSchema implements Schema
{
    /** @var RelationshipSchema[] */
    private $relationships;

    /**
     * @param RelationshipSchema[] $relationships
     */
    public function __construct(array $relationships)
    {
        Assertion::allIsInstanceOf($relationships, RelationshipSchema::class);
        $this->relationships = $relationships;
    }

    public function toOpenApi(): array
    {
        $relationships = [];
        $requiredRelationships = [];

        /** @var RelationshipSchema $relationshipSchema */
        foreach ($this->relationships as $relationshipSchema) {
            $relationships[$relationshipSchema->getName()] = $relationshipSchema->toOpenApi();
            if (false === $relationshipSchema->isNullable()) {
                $requiredRelationships[] = $relationshipSchema->getName();
            }
        }

        if (true === empty($relationships)) {
            return [];
        }

        $openApi = [
            'type' => 'object',
            'nullable' => false,
            'properties' => $relationships,
        ];

        if (false === empty($requiredRelationships)) {
            $openApi['required'] = $requiredRelationships;
        }

        /*
         * @todo Should we support optional relationships?
         *
         * Resource relationships should always be present and therefore are required in the generated schema. Should not
         * be ommited from any variation of a resource schema (read, create, update).
         * In some Create resource is different from the Read resource (e.g. you can get the `user` relationship but you
         * can't create or update it) we can create two separate API models: create and read.
         *
         * Example implementation:
         * ```php
         *  $requiredRelationships = array_keys(array_filter($relationshipSchemas,
         *      function (RelationshipSchema $relationshipSchema) {
         *          return $relationshipSchema->isRequired();
         *      }));
         *  if (false === empty($requiredRelationships)) {
         *      $openApi['required'] = $requiredRelationships;
         *  }
         * ```
         */

        return $openApi;
    }
}
