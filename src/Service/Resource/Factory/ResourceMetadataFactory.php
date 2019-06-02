<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Factory;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation as Annotation;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\AttributeMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\RelationshipMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\Definition\ResourceMetadataFactoryInterface;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint as JsonApiConstraint;

class ResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    /** @var Reader */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws InvalidResourceMappingException
     */
    public function getClassMetadata(string $class): ResourceMetadata
    {
        $reflection = new ReflectionClass($class);

        [$resourceConstraints, $attributeMetadata, $relationshipMetadata] = $this->loadMetadata($reflection);

        $this->validate($attributeMetadata, $relationshipMetadata);

        return new ResourceMetadata($resourceConstraints, $attributeMetadata, $relationshipMetadata);
    }

    /**
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws InvalidResourceMappingException
     */
    public function getResourceMetadata($resource): ResourceMetadata
    {
        $reflection = new ReflectionClass($resource);

        [$resourceConstraints, $attributeMetadata, $relationshipMetadata] = $this->loadMetadata($reflection);

        $this->validate($attributeMetadata, $relationshipMetadata);

        return new ResourceMetadata($resourceConstraints, $attributeMetadata, $relationshipMetadata);
    }

    /**
     * @throws InvalidResourceMappingException
     */
    private function loadMetadata(ReflectionClass $reflection): array
    {
        $attributeMetadata = [];
        $relationshipMetadata = [];

        $properties = $reflection->getProperties();

        $classAnnotations = $this->reader->getClassAnnotations($reflection);
        $classAnnotations = new ArrayCollection($classAnnotations);
        $resourceConstraints = $classAnnotations->filter(function ($annotation) {
            return $annotation instanceof Constraint;
        })->getValues();

        /** @var ReflectionProperty $property */
        foreach ($properties as $property) {
            $propertyAnnotations = $this->reader->getPropertyAnnotations($property);
            $propertyAnnotations = new ArrayCollection($propertyAnnotations);

            $constraintAnnotations = $propertyAnnotations->filter(function ($annotation) {
                return $annotation instanceof Constraint;
            })->getValues();

            $attributeAnnotations = $propertyAnnotations->filter(function ($annotation) {
                return $annotation instanceof Annotation\Attribute;
            });

            $relationshipAnnotations = $propertyAnnotations->filter(function ($annotation) {
                return $annotation instanceof Annotation\Relationship;
            });

            if (false === $attributeAnnotations->isEmpty() && false === $relationshipAnnotations->isEmpty()) {
                $message = sprintf(
                    'Property `%s` can\'t be attribute and relationship in the same time',
                    $property->getName()
                );
                throw new InvalidResourceMappingException($message);
            }

            if ($attributeAnnotations->count() > 1) {
                $message = sprintf('More than 1 Attribute Annotation found for property `%s`', $property->getName());
                throw new InvalidResourceMappingException($message);
            }

            if ($relationshipAnnotations->count() > 1) {
                $message = sprintf('More than 1 Relationship Annotation found for property `%s`', $property->getName());
                throw new InvalidResourceMappingException($message);
            }

            if (false === $attributeAnnotations->isEmpty()) {
                $attributeMetadata[] = $this->buildAttributeMetadata(
                    $property,
                    $attributeAnnotations->first(),
                    $constraintAnnotations
                );
            }

            if (false === $relationshipAnnotations->isEmpty()) {
                $relationshipMetadata[] = $this->buildRelationshipMetadata(
                    $property,
                    $relationshipAnnotations->first(),
                    $constraintAnnotations
                );
            }
        }

        return [
            $resourceConstraints,
            $attributeMetadata,
            $relationshipMetadata,
        ];
    }

    private function buildAttributeMetadata(
        ReflectionProperty $property,
        Annotation\Attribute $attributeAnnotation,
        array $constraintAnnotations
    ): AttributeMetadata {
        // Allow name to be overridden by the annotation attribute `name`, with fallback to the property name
        $name = $attributeAnnotation->name ?? $property->getName();

        // @todo Idea: add attribute type validation constraint based on the property type (docblock)?

        return new AttributeMetadata(
            $name,
            $property->getName(),
            $constraintAnnotations
        );
    }

    /**
     * @throws InvalidResourceMappingException
     */
    private function buildRelationshipMetadata(
        ReflectionProperty $property,
        Annotation\Relationship $relationshipAnnotation,
        array $constraintAnnotations
    ): RelationshipMetadata {
        // Allow name to be overridden by the annotation attribute `name`, with fallback to the property name
        $name = $relationshipAnnotation->name ?? $property->getName();
        /** @var string|null $relatedResourceType */
        $relatedResourceType = $relationshipAnnotation->type;

        if (null === $relatedResourceType) {
            // @todo Idea: if the type is not set, library could use "best effort" method and guess the type from the
            // @todo property name. However, this behavior should be explicitly set by the dev to avoid confusion and voodoo magic

            $message = sprintf('Resource type for `%s` is not defined', $property->getName());
            throw new InvalidResourceMappingException($message);
        }

        $constraintAnnotations[] = $relationshipAnnotation->isToMany() ? new JsonApiConstraint\ToMany() : new JsonApiConstraint\ToOne();
        $constraintAnnotations[] = JsonApiConstraint\ResourceType::make($relatedResourceType);

        return new RelationshipMetadata(
            $name,
            $relatedResourceType,
            $property->getName(),
            $constraintAnnotations,
            $relationshipAnnotation->isToMany()
        );
    }

    /**
     * @throws InvalidResourceMappingException
     */
    private function validate(array $attributeMetadata, array $relationshipMetadata)
    {
        /**
         * In other words, a resource can not have an attribute and relationship with the same name,
         * nor can it have an attribute or relationship named type or id.
         * https://jsonapi.org/format/#document-resource-object-fields
         */
        $reservedNames = ['id', 'type'];
        $names = [];

        $metadata = array_merge($attributeMetadata, $relationshipMetadata);

        /** @var RelationshipMetadata|AttributeMetadata $metadatum */
        foreach ($metadata as $metadatum) {
            $name = $metadatum->getName();

            if (true === in_array($name, $reservedNames)) {
                $message = sprintf('Resource can\'t use reserved attribute or relationship name `%s`', $name);
                throw new InvalidResourceMappingException($message);
            }

            if (true === in_array($name, $names)) {
                $message = sprintf('Resource already has attribute or relationship named `%s`', $name);
                throw new InvalidResourceMappingException($message);
            }

            $names[] = $name;
        }
    }
}
