<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Validation\ConstraintValidator;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierCollectionInterface;
use Undabot\JsonApi\Model\Resource\ResourceIdentifierInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Resource\Validation\Constraint\ResourceType;

class ResourceTypeValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param ResourceType $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        Assertion::isInstanceOf($constraint, ResourceType::class);

        // If the given value is RelationshipInterface, validate its end data - either a single or multiple resource identifiers
        if ($value instanceof RelationshipInterface) {
            $value = $value->getData()->getData();
        }

        // If the given value is ResourceInterface, validate its type
        if ($value instanceof ResourceInterface) {
            $value = $value->getType();
        }

        // If the given value is ResourceIdentifierInterface, validate its type
        if ($value instanceof ResourceIdentifierInterface) {
            $value = $value->getType();
        }

        // If the given value is ResourceCollectionInterface, validate array of its types
        if ($value instanceof ResourceCollectionInterface) {
            $value = array_map(function (ResourceInterface $resource) {
                return $resource->getType();
            }, iterator_to_array($value));
        }

        // If the given value is ResourceIdentifierCollectionInterface, validate array of its types
        if ($value instanceof ResourceIdentifierCollectionInterface) {
            $value = array_map(function (ResourceIdentifierInterface $resource) {
                return $resource->getType();
            }, iterator_to_array($value));
        }

        if (true === is_array($value)) {
            $this->validateArrayOfTypes($value, $constraint);

            return;
        }

        if (null === $value) {
            /**
             * This validator can't validate null value since it doesn't know whether the value is optional or not.
             * If the value (e.g. for non-optional relationship) should not be empty, there should exist NotBlank
             * constraint on the relationship property
             */
            return;
        }

        if (false === is_string($value)) {
            $this->context->buildViolation('Resource type must be a string value.')
                ->addViolation();

            return;
        }

        if ($value !== $constraint->getType()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ given }}', $value)
                ->setParameter('{{ expected }}', $constraint->getType())
                ->addViolation();
        }
    }

    private function validateArrayOfTypes(array $types, ResourceType $constraint)
    {
        $invalidValues = [];
        foreach ($types as $type) {
            if ($type !== $constraint->getType()) {
                $invalidValues[$type];
            }
        }

        if (0 === count($invalidValues)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ given }}', implode(', ', $invalidValues))
            ->setParameter('{{ expected }}', $constraint->getType())
            ->addViolation();
    }
}
