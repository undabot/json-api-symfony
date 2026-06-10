<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Factory;

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
    /**
     * @throws InvalidResourceMappingException
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public function getClassMetadata(string $class): ResourceMetadata
    {
        if (false === class_exists($class)) {
            throw new \InvalidArgumentException('Given class does not exists');
        }

        $reflection = new \ReflectionClass($class);

        [$resourceConstraints, $attributeMetadata, $relationshipMetadata] = $this->loadMetadata($reflection);

        $this->validate($attributeMetadata, $relationshipMetadata);

        return new ResourceMetadata($resourceConstraints, $attributeMetadata, $relationshipMetadata);
    }

    /**
     * @throws InvalidResourceMappingException
     * @throws \ReflectionException
     */
    public function getInstanceMetadata(ApiModel $apiModel): ResourceMetadata
    {
        $reflection = new \ReflectionClass($apiModel);

        [$resourceConstraints, $attributeMetadata, $relationshipMetadata] = $this->loadMetadata($reflection);

        $this->validate($attributeMetadata, $relationshipMetadata);

        return new ResourceMetadata($resourceConstraints, $attributeMetadata, $relationshipMetadata);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     *
     * @return mixed[]
     *
     * @throws InvalidResourceMappingException
     */
    private function loadMetadata(\ReflectionClass $reflection): array
    {
        $attributeMetadata = [];
        $relationshipMetadata = [];

        $resourceConstraints = $this->instantiateAttributes($reflection, Constraint::class);

        foreach ($reflection->getProperties() as $property) {
            $attributeReflAttributes = $property->getAttributes(Annotation\Attribute::class, \ReflectionAttribute::IS_INSTANCEOF);
            $relationshipReflAttributes = $property->getAttributes(Annotation\Relationship::class, \ReflectionAttribute::IS_INSTANCEOF);

            if ([] !== $attributeReflAttributes && [] !== $relationshipReflAttributes) {
                $message = \sprintf(
                    'Property `%s` can\'t be attribute and relationship in the same time',
                    $property->getName()
                );

                throw new InvalidResourceMappingException($message);
            }

            if (\count($attributeReflAttributes) > 1) {
                $message = \sprintf('More than 1 Attribute Annotation found for property `%s`', $property->getName());

                throw new InvalidResourceMappingException($message);
            }

            if (\count($relationshipReflAttributes) > 1) {
                $message = \sprintf('More than 1 Relationship Annotation found for property `%s`', $property->getName());

                throw new InvalidResourceMappingException($message);
            }

            $constraintAttributes = $this->instantiateAttributes($property, Constraint::class);

            if ([] !== $attributeReflAttributes) {
                $attributeMetadata[] = $this->buildAttributeMetadata(
                    $property,
                    $attributeReflAttributes[0]->newInstance(),
                    $constraintAttributes
                );
            }

            if ([] !== $relationshipReflAttributes) {
                $relationshipMetadata[] = $this->buildRelationshipMetadata(
                    $property,
                    $relationshipReflAttributes[0]->newInstance(),
                    $constraintAttributes
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
     * @template T of object
     *
     * @param \ReflectionClass<object>|\ReflectionProperty $reflection
     * @param class-string<T>                              $attributeClass
     *
     * @return list<T>
     */
    private function instantiateAttributes(\ReflectionClass|\ReflectionProperty $reflection, string $attributeClass): array
    {
        return array_map(
            static fn (\ReflectionAttribute $attribute) => $attribute->newInstance(),
            $reflection->getAttributes($attributeClass, \ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    /**
     * @param Constraint[] $constraints
     */
    private function buildAttributeMetadata(
        \ReflectionProperty $property,
        Annotation\Attribute $attributeAnnotation,
        array $constraints,
    ): AttributeMetadata {
        // Allow name to be overridden by the attribute argument `name`, with fallback to the property name
        $name = $attributeAnnotation->name ?? $property->getName();

        return new AttributeMetadata(
            $name,
            $property->getName(),
            $constraints,
            $attributeAnnotation
        );
    }

    /**
     * @param Constraint[] $constraints
     *
     * @throws InvalidResourceMappingException
     */
    private function buildRelationshipMetadata(
        \ReflectionProperty $property,
        Annotation\Relationship $relationshipAnnotation,
        array $constraints,
    ): RelationshipMetadata {
        // Allow name to be overridden by the attribute argument `name`, with fallback to the property name
        $name = $relationshipAnnotation->name ?? $property->getName();
        $relatedResourceType = $relationshipAnnotation->type;

        if (null === $relatedResourceType) {
            $message = \sprintf('Resource type for `%s` is not defined', $property->getName());

            throw new InvalidResourceMappingException($message);
        }

        $constraints[] = $relationshipAnnotation->isToMany() ? new JsonApiConstraint\ToMany() : new JsonApiConstraint\ToOne();
        $constraints[] = JsonApiConstraint\ResourceType::make($relatedResourceType);

        return new RelationshipMetadata(
            $name,
            $relatedResourceType,
            $property->getName(),
            $constraints,
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
                $message = \sprintf('Resource can\'t use reserved attribute or relationship name `%s`', $name);

                throw new InvalidResourceMappingException($message);
            }

            if (true === \in_array($name, $names, true)) {
                $message = \sprintf('Resource already has attribute or relationship named `%s`', $name);

                throw new InvalidResourceMappingException($message);
            }

            $names[] = $name;
        }
    }
}
