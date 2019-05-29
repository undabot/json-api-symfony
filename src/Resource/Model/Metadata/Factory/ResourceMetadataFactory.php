<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource\Annotation as Annotation;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\AttributeMetadata;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\RelationshipMetadata;
use Undabot\SymfonyJsonApi\Resource\Model\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Resource\Validation\Constraint\ResourceType;
use Undabot\SymfonyJsonApi\Resource\Validation\Constraint\ToMany;
use Undabot\SymfonyJsonApi\Resource\Validation\Constraint\ToOne;

class ResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    /** @var Reader */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
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
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
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
     * @throws \Doctrine\Common\Annotations\AnnotationException
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
            $annotations = $this->reader->getPropertyAnnotations($property);
            $annotations = new ArrayCollection($annotations);

            $constraintAnnotations = $annotations->filter(function ($annotation) {
                return $annotation instanceof Constraint;
            })->getValues();

            $attributeAnnotations = $annotations->filter(function ($annotation) {
                return $annotation instanceof Annotation\Attribute;
            });

            $relationshipAnnotations = $annotations->filter(function ($annotation) {
                return $annotation instanceof Annotation\Relationship;
            });

            if (false === $attributeAnnotations->isEmpty() && false === $relationshipAnnotations->isEmpty()) {
                $message = sprintf('Property `%s` can\'t be attribute and relationship in the same time',
                    $property->getName());
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
        // Allow name to be overridden by the annotation, fall back to the property name instead
        $name = $attributeAnnotation->name ?? $property->getName();

        // @todo add attribute type validation from the property typehint?

        return new AttributeMetadata(
            $name,
            $property->getName(),
            $constraintAnnotations
        );
    }

    private function buildRelationshipMetadata(
        ReflectionProperty $property,
        Annotation\Relationship $relationshipAnnotation,
        array $constraintAnnotations
    ): RelationshipMetadata {
        // Allow name to be overridden by the annotation, fall back to the property name instead
        $name = $relationshipAnnotation->name ?? $property->getName();
        $relatedResourceType = $relationshipAnnotation->type;

        if (null === $relatedResourceType) {
            // @todo deduct the type from property name?

            $message = sprintf('Resource type for `%s` is not defined', $property->getName());
            throw new InvalidResourceMappingException($message);
        }

        $constraintAnnotations[] = $relationshipAnnotation->isToMany() ? new ToMany() : new ToOne();
        $constraintAnnotations[] = ResourceType::make($relatedResourceType);

        // @todo add resource toMany / toOne constraint
        // @todo add resource type constraint

        return new RelationshipMetadata(
            $name,
            $relatedResourceType,
            $property->getName(),
            $constraintAnnotations,
            $relationshipAnnotation->isToMany()
        );
    }

    /**
     * @param AttributeMetadata[] $attributeMetadata
     * @param RelationshipMetadata[] $relationshipMetadata
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
