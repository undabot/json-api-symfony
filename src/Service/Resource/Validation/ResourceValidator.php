<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation;

use Doctrine\Common\Annotations\AnnotationException;
use ReflectionException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Undabot\JsonApi\Definition\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Model\Resource\FlatResource;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Exception\ModelInvalid;

class ResourceValidator
{
    private ResourceMetadataFactory $metadataFactory;

    private ValidatorInterface $validator;

    public function __construct(ResourceMetadataFactory $metadataFactory, ValidatorInterface $validator)
    {
        $this->metadataFactory = $metadataFactory;
        $this->validator = $validator;
    }

    /**
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws InvalidResourceMappingException
     */
    public function validate(ResourceInterface $resource, string $class): ResourceValidationViolations
    {
        $metadata = $this->metadataFactory->getClassMetadata($class);
        $flatResource = new FlatResource($resource);

        $resourceValidationViolations = $this->validator->validate($resource, $metadata->getResourceConstraints());
        $attributesValidationViolations = $this->validateAttributes($flatResource, $metadata);
        $relationshipValidationViolations = $this->validateRelationships($flatResource, $metadata);

        return new ResourceValidationViolations(
            $resourceValidationViolations,
            $attributesValidationViolations,
            $relationshipValidationViolations
        );
    }

    /**
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws InvalidResourceMappingException
     * @throws ModelInvalid
     */
    public function assertValid(ResourceInterface $resource, string $class): void
    {
        $errors = $this->validate($resource, $class);
        if (0 === $errors->count()) {
            return;
        }

        throw new ModelInvalid($resource, $errors);
    }

    /**
     * Relationships need two levels of validation:.
     *
     * 1. Value-level validation performed on the raw relationship values (string or string[]) against the
     *    Collection of standard value constraints (e.g. NotEmpty, custom constraints validating that IDs actually exist)
     *    see https://symfony.com/doc/current/validation/raw_values.html
     *    Validation is performed with `allowMissingFields = false` to report missing relationship keys
     *
     * 2. RelationshipInterface-level validation performed on the Relationship objects against constraints that need more
     *    context for validation (i.e. where the raw relationship value, string or string[], is not enough)
     *    e.g. ResourceType constraint.
     *    Validation is performed with `allowMissingFields = true` to avoid duplicate errors for missing relationship keys
     */
    private function validateRelationships(
        FlatResource $flatResource,
        ResourceMetadata $metadata
    ): ConstraintViolationListInterface {
        $relationshipValueValidationViolations = $this->validator->validate(
            ['data' => ['relationships' => $flatResource->getRelationships()]],
            new Collection([
                'data' => new Collection([
                    'relationships' => new Collection([
                        'allowMissingFields' => false,
                        'allowExtraFields' => true,
                        'fields' => $metadata->getRelationshipsValueConstraints(),
                    ]),
                ]),
            ])
        );

        $relationshipObjectValidationViolations = $this->validator->validate(
            ['data' => ['relationships' => $flatResource->getIndexedRelationshipObjects()]],
            new Collection([
                'data' => new Collection([
                    'relationships' => new Collection([
                        'allowMissingFields' => true,
                        'allowExtraFields' => true,
                        'fields' => $metadata->getRelationshipsObjectConstraints(),
                    ]),
                ]),
            ])
        );

        $relationshipValidationViolations = new ConstraintViolationList();
        $relationshipValidationViolations->addAll($relationshipValueValidationViolations);
        $relationshipValidationViolations->addAll($relationshipObjectValidationViolations);

        return $relationshipValidationViolations;
    }

    private function validateAttributes(
        FlatResource $flatResource,
        ResourceMetadata $metadata
    ): ConstraintViolationListInterface {
        return $this->validator->validate(
            ['data' => ['attributes' => $flatResource->getAttributes()]],
            new Collection([
                'data' => new Collection([
                    'attributes' => new Collection([
                        'allowMissingFields' => false,
                        'allowExtraFields' => true,
                        'fields' => $metadata->getAttributesConstraints(),
                    ]),
                ]),
            ])
        );
    }
}
