<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Service;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\RelationshipSchema;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\RelationshipMetadata;

class RelationshipSchemaFactory
{
    public function make(RelationshipMetadata $metadata): RelationshipSchema
    {
        $relationshipAnnotation = $metadata->getRelationshipAnnotation();
        $nullable = $relationshipAnnotation->nullable;

        /** @var Constraint $constraint */
        foreach ($metadata->getConstraints() as $constraint) {
            if ($constraint instanceof NotNull) {
                $nullable = false;
            }

            if ($constraint instanceof NotBlank) {
                $nullable = $constraint->allowNull;
            }

            if ($constraint instanceof NotBlank && null === $nullable) {
                $nullable = $constraint->allowNull;
            }
        }

        if (null === $nullable) {
            $nullable = false;
        }

        return new RelationshipSchema(
            $metadata->getName(),
            $relationshipAnnotation->description,
            $nullable,
            $relationshipAnnotation->type,
            $relationshipAnnotation->isToMany()
        );
    }
}
