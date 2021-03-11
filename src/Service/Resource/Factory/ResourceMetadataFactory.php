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
use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\AttributeMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\Exception\InvalidResourceMappingException;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\RelationshipMetadata;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;
use Undabot\SymfonyJsonApi\Service\Resource\Factory\Definition\ResourceMetadataFactoryInterface;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint as JsonApiConstraint;

class ResourceMetadataFactory implements ResourceMetadataFactoryInterface
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @throws AnnotationException
     * @throws ReflectionException
     * @throws InvalidResourceMappingException
     * @throws \InvalidArgumentException
     */
    public function getClassMetadata(string $class): ResourceMetadata
    {
        if (false === class_exists($class)) {
            throw new \InvalidArgumentException('Given class does not exists');
        }

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
    public function getInstanceMetadata(ApiModel $apiModel): ResourceMetadata
    {
        $reflection = new ReflectionClass($apiModel);

        [$resourceConstraints, $attributeMetadata, $relationshipMetadata] = $this->loadMetadata($reflection);

        $this->validate($attributeMetadata, $relationshipMetadata);

        return new ResourceMetadata($resourceConstraints, $attributeMetadata, $relationshipMetadata);
    }

    /**
     * @throws InvalidResourceMappingException
     *
     * @return mixed[]
     */
    private function loadMetadata(ReflectionClass $reflection): array
    {
        $attributeMetadata = [];
        $relationshipMetadata = [];

        $properties = $reflection->getProperties();

        $classAnnotations = $this->reader->getClassAnnotations($reflection);
        $classAnnotations = new ArrayCollection($classAnnotations);
        $resourceConstraints = $classAnnotations->filter(static function ($annotation) {
            return $annotation instanceof Constraint;
        })->getValues();

        /** @var ReflectionProperty $property */
        foreach ($properties as $property) {
            $propertyAnnotations = $this->reader->getPropertyAnnotations($property);
            $propertyAnnotations = new ArrayCollection($propertyAnnotations);

            /** @var array<int,Constraint> $constraintAnnotations */
            $constraintAnnotations = $propertyAnnotations->filter(static function ($annotation) {
                return $annotation instanceof Constraint;
            })->getValues();

            $attributeAnnotations = $propertyAnnotations->filter(static function ($annotation) {
                return $annotation instanceof Annotation\Attribute;
            });

            $relationshipAnnotations = $propertyAnnotations->filter(static function ($annotation) {
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
                /** @var Annotation\Attribute $attributeAnnotation */
                $attributeAnnotation = $attributeAnnotations->first();
                $attributeMetadata[] = $this->buildAttributeMetadata(
                    $property,
                    $attributeAnnotation,
                    $constraintAnnotations
                );
            }

            if (false === $relationshipAnnotations->isEmpty()) {
                /** @var Annotation\Relationship $relationshipAnnotation */
                $relationshipAnnotation = $relationshipAnnotations->first();
                $relationshipMetadata[] = $this->buildRelationshipMetadata(
                    $property,
                    $relationshipAnnotation,
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

    /**
     * @param Constraint[] $constraintAnnotations
     */
    private function buildAttributeMetadata(
        ReflectionProperty $property,
        Annotation\Attribute $attributeAnnotation,
        array $constraintAnnotations
    ): AttributeMetadata {
        // Allow name to be overridden by the annotation attribute `name`, with fallback to the property name
        $name = $attributeAnnotation->name ?? $property->getName();

        // @todo should we infer nullability from typehint?
//        $docComment = $property->getDocComment();
//        $nullable = null;
//        if (false === empty($docComment)) {
//            preg_match_all('/@var (.*)/m', $docComment, $result);
//            $nullable = strpos($result[1][0] ?? '', 'null') !== false;
//        }
        // @todo add support for PHP 7.4 types and nullability check

        // @todo Idea: add attribute type validation constraint based on the property type (docblock)?

        return new AttributeMetadata(
            $name,
            $property->getName(),
            $constraintAnnotations,
            $attributeAnnotation
        );
    }

    /**
     * @param Constraint[] $constraintAnnotations
     *
     * @throws InvalidResourceMappingException
     */
    private function buildRelationshipMetadata(
        ReflectionProperty $property,
        Annotation\Relationship $relationshipAnnotation,
        array $constraintAnnotations
    ): RelationshipMetadata {
        // Allow name to be overridden by the annotation attribute `name`, with fallback to the property name
        $name = $relationshipAnnotation->name ?? $property->getName();
        /** @var null|string $relatedResourceType */
        $relatedResourceType = $relationshipAnnotation->type;

        if (null === $relatedResourceType) {
            /**
             * @todo Idea: if the type is not set, library could use "best effort" method and guess the type from the
             * @todo property name. However, this behavior should be explicitly set by the dev to avoid confusion and voodoo magic
             */
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
            $relationshipAnnotation->isToMany(),
            $relationshipAnnotation
        );
    }

    /**
     * @param AttributeMetadata[]    $attributeMetadata
     * @param RelationshipMetadata[] $relationshipMetadata
     *
     * @throws InvalidResourceMappingException
     */
    private function validate(array $attributeMetadata, array $relationshipMetadata): void
    {
        /**
         * In other words, a resource can not have an attribute and relationship with the same name,
         * nor can it have an attribute or relationship named type or id.
         * https://jsonapi.org/format/#document-resource-object-fields.
         */
        $reservedNames = ['id', 'type'];
        $names = [];

        $metadata = array_merge($attributeMetadata, $relationshipMetadata);

        /** @var AttributeMetadata|RelationshipMetadata $metadatum */
        foreach ($metadata as $metadatum) {
            $name = $metadatum->getName();

            if (true === \in_array($name, $reservedNames, true)) {
                $message = sprintf('Resource can\'t use reserved attribute or relationship name `%s`', $name);

                throw new InvalidResourceMappingException($message);
            }

            if (true === \in_array($name, $names, true)) {
                $message = sprintf('Resource already has attribute or relationship named `%s`', $name);

                throw new InvalidResourceMappingException($message);
            }

            $names[] = $name;
        }
    }
}
