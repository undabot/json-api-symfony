<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Exception\SchemaCollectionException;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Resource\ResourceSchemaSet;

class SchemaCollection
{
    /** @var array<string,mixed>
     */
    private static array $schemas = [];

    public static function add(string $resourceClass, ResourceSchemaSet $resourceSchemaSet): void
    {
        $resourceClass = static::normalizeClassName($resourceClass);
        if (true === self::exists($resourceClass)) {
            throw SchemaCollectionException::resourceAlreadyExists();
        }

        static::$schemas[$resourceClass] = $resourceSchemaSet;
    }

    public static function exists(string $resourceClass): bool
    {
        $resourceClass = static::normalizeClassName($resourceClass);

        return isset(static::$schemas[$resourceClass]);
    }

    public static function get(string $className): ResourceSchemaSet
    {
        $className = static::normalizeClassName($className);

        return static::$schemas[$className];
    }

    /**
     * @return mixed[]
     */
    public static function toOpenApi(): array
    {
        $data = [];

        /** @var ResourceSchemaSet $schemaSet */
        foreach (static::$schemas as $schemaSet) {
            if (null !== $schemaSet->getIdentifier()) {
                $data[$schemaSet->getIdentifier()->getName()] = $schemaSet->getIdentifier()->toOpenApi();
            }

            if (null !== $schemaSet->getReadModel()) {
                $data[$schemaSet->getReadModel()->getName()] = $schemaSet->getReadModel()->toOpenApi();
            }

            if (null !== $schemaSet->getCreateModel()) {
                $data[$schemaSet->getCreateModel()->getName()] = $schemaSet->getCreateModel()->toOpenApi();
            }

            if (null !== $schemaSet->getUpdateModel()) {
                $data[$schemaSet->getUpdateModel()->getName()] = $schemaSet->getUpdateModel()->toOpenApi();
            }
        }

        return $data;
    }

    private static function normalizeClassName(string $className): string
    {
        $targetClassName = $className;
        if ('\\' === $targetClassName[0]) {
            $targetClassName = mb_substr($targetClassName, 1);
        }

        return $targetClassName;
    }
}
