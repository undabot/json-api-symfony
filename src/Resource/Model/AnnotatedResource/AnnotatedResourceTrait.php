<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Model\AnnotatedResource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use ReflectionClass;
use ReflectionProperty;
use Undabot\JsonApi\Factory\RelationshipDataFactory;
use Undabot\JsonApi\Model\Link\LinkInterface;
use Undabot\JsonApi\Model\Meta\MetaInterface;
use Undabot\JsonApi\Model\Resource\Attribute\Attribute;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollection;
use Undabot\JsonApi\Model\Resource\Attribute\AttributeCollectionInterface;
use Undabot\JsonApi\Model\Resource\Relationship\Relationship;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollection;
use Undabot\JsonApi\Model\Resource\Relationship\RelationshipCollectionInterface;

trait AnnotatedResourceTrait
{
    public function getAttributes(): ?AttributeCollectionInterface
    {
        $reflect = new ReflectionClass($this);
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $properties = $reflect->getProperties();

        $attributes = [];

        /** @var ReflectionProperty $property */
        foreach ($properties as $property) {
            $annotations = $annotationReader->getPropertyAnnotations($property);
            $annotations = new ArrayCollection($annotations);

            $attributeAnnotations = $annotations->filter(function ($annotation) {
                return $annotation instanceof Annotation\Attribute;
            });

            if (true === $attributeAnnotations->isEmpty()) {
                continue;
            }

            if ($attributeAnnotations->count() > 1) {
                $message = sprintf('More than 1 attribute Annotation found for property %s', $property->getName());
                throw new LogicException($message);
            }

            $attributeAnnotation = $attributeAnnotations->first();
            $name = $attributeAnnotation->name ?? $property->getName();
            $value = $property->getValue($this);

            $attributes[] = new Attribute($name, $value);
        }

        return new AttributeCollection($attributes);
    }

    public function getRelationships(): ?RelationshipCollectionInterface
    {
        $reflect = new ReflectionClass($this);
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        $properties = $reflect->getProperties();

        $relationships = [];

        /** @var ReflectionProperty $property */
        foreach ($properties as $property) {
            $annotations = $annotationReader->getPropertyAnnotations($property);
            $annotations = new ArrayCollection($annotations);

            $relationshipAnnotations = $annotations->filter(function ($annotation) {
                return $annotation instanceof Annotation\Relationship;
            });

            if (true === $relationshipAnnotations->isEmpty()) {
                continue;
            }

            if (1 < $relationshipAnnotations->count()) {
                $message = sprintf('More than 1 relationship Annotation found for property %s', $property->getName());
                throw new LogicException($message);
            }

            /** @var Annotation\Relationship $relationsihpAnnotation */
            $relationsihpAnnotation = $relationshipAnnotations->first();
            $name = $relationsihpAnnotation->name ?? $property->getName();
            $value = $property->getValue($this);

            $factory = new RelationshipDataFactory();
            $relationshipData = $factory->make(
                $relationsihpAnnotation->type,
                $relationsihpAnnotation->isToMany(),
                $value
            );

            $relationships[] = new Relationship($name, null, $relationshipData);
        }

        return new RelationshipCollection($relationships);
    }

    public function getSelfUrl(): ?LinkInterface
    {
        return null;
    }

    public function getMeta(): ?MetaInterface
    {
        return null;
    }
}
