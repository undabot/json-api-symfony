<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator;

use Assert\Assertion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Undabot\JsonApi\Definition\Model\Resource\Relationship\RelationshipInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceIdentifierCollectionInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceIdentifierInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

class ResourceTypeValidator extends ConstraintValidator
{
    /**
     * @param ResourceType $constraint
     * @param mixed        $value
     */
    public function validate($value, Constraint $constraint): void
    {
        Assertion::isInstanceOf($constraint, ResourceType::class);

        // If the given value is RelationshipInterface, validate its end data - either a single or multiple resource identifiers
        if ($value instanceof RelationshipInterface && null !== $value->getData()) {
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
            $value = array_map(static function (ResourceInterface $resource) {
                return $resource->getType();
            }, iterator_to_array($value));
        }

        // If the given value is ResourceIdentifierCollectionInterface, validate array of its types
        if ($value instanceof ResourceIdentifierCollectionInterface) {
            $value = array_map(static function (ResourceIdentifierInterface $resource) {
                return $resource->getType();
            }, iterator_to_array($value));
        }

        // If array of values is given, validate that all elements in the array are of the same type.
        if (true === \is_array($value)) {
            $this->validateArrayOfTypes($value, $constraint);

            return;
        }

        if (null === $value) {
            /*
             * This validator can't validate null value since it doesn't know whether the value is optional or not.
             * If the value (e.g. for non-optional relationship) should not be empty, there should exist NotBlank
             * constraint on the relationship property
             */
            return;
        }

        if (false === \is_string($value)) {
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

    /**
     * @param string[] $types
     */
    private function validateArrayOfTypes(array $types, ResourceType $constraint): void
    {
        $invalidValues = [];
        foreach ($types as $type) {
            if ($type !== $constraint->getType()) {
                $invalidValues[] = $type;
            }
        }

        if (0 === \count($invalidValues)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ given }}', implode(', ', $invalidValues))
            ->setParameter('{{ expected }}', $constraint->getType())
            ->addViolation();
    }
}
