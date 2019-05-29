<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Validation;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;
use Undabot\SymfonyJsonApi\Resource\FlatResource;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory\ResourceMetadataFactory;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\ResourceMetadata;

class ResourceValidator
{
    /** @var ResourceMetadataFactory */
    private $metadataFactory;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ResourceMetadataFactory $metadataFactory, ValidatorInterface $validator)
    {
        $this->metadataFactory = $metadataFactory;
        $this->validator = $validator;
    }

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
     * Relationships need two levels of validation:
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

        /**
         * Relationship values are
         */
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
        $attributesValidationViolations = $this->validator->validate(
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

        return $attributesValidationViolations;
    }
}
