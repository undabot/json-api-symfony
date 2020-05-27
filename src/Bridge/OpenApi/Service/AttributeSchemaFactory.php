<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Service;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\AttributeSchema;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\AttributeMetadata;

class AttributeSchemaFactory
{
    public function make(AttributeMetadata $metadata): AttributeSchema
    {
        $type = 'string';

        $attributeAnnotation = $metadata->getAttributeAnnotation();
        $nullable = $attributeAnnotation->nullable;

        /** @var Constraint $constraint */
        foreach ($metadata->getConstraints() as $constraint) {
            if ($constraint instanceof Type) {
                $type = $constraint->type;
            }

            if ($constraint instanceof NotNull) {
                $nullable = false;
            }

            if ($constraint instanceof NotBlank && false === $nullable) {
                $nullable = $constraint->allowNull;
            }
        }

        return new AttributeSchema(
            $metadata->getName(),
            $type,
            $nullable,
            $attributeAnnotation->description,
            $attributeAnnotation->format,
            $attributeAnnotation->example
        );
    }
}
