<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Factory;

use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Exception\FilterRequiredException;
use Undabot\SymfonyJsonApi\Exception\PaginationRequiredException;
use Undabot\SymfonyJsonApi\Exception\QueryParamTransformerNotDefinedException;

abstract class AbstractQueryFactory
{
    /** @var array<string, mixed> */
    private array $defaultPropertyValues = [];

    /** @param class-string $queryClass */
    public function fromRequest(GetResourceCollectionRequestInterface $request, string $queryClass): object
    {
        $pagination = $request->getPagination();
        $filterSet = $request->getFilterSet();

        $reflectionClass = new \ReflectionClass($queryClass);
        $args = [];

        foreach ($reflectionClass->getConstructor()?->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            if ('offset' === $parameterName) {
                $offset = $pagination?->getOffset();

                if (null === $offset && false === $parameter->allowsNull()) {
                    throw PaginationRequiredException::noPaginationProvided();
                }

                $args[] = $offset;

                continue;
            }

            if ('size' === $parameterName) {
                $size = $pagination?->getSize();

                if (null === $size && false === $parameter->allowsNull()) {
                    throw PaginationRequiredException::noPaginationProvided();
                }

                $args[] = $size;

                continue;
            }

            $argValue = $filterSet?->getFilterValue($parameterName);

            if (
                null === $argValue
                && false === $parameter->allowsNull()
                && (
                    false === \array_key_exists($parameterName, $this->defaultPropertyValues)
                    || null === $this->defaultPropertyValues[$parameterName]
                )
            ) {
                throw FilterRequiredException::withName($parameterName);
            }

            if (null === $argValue && \array_key_exists($parameterName, $this->defaultPropertyValues)) {
                $argValue = $this->defaultPropertyValues[$parameterName];
            }

            $type = $parameter->getType();

            if ($type instanceof \ReflectionNamedType) {
                if (true === $type->isBuiltin()) {
                    $args[] = $argValue;

                    continue;
                }

                if (false === \array_key_exists($type->getName(), $this->getMap())) {
                    /** @var class-string $className */
                    $className = $type->getName();

                    throw QueryParamTransformerNotDefinedException::forClass($className);
                }

                if (null !== $argValue) {
                    $argValue = $this->getMap()[$type->getName()]($argValue);
                }
            }

            $args[] = $argValue;
        }

        return $reflectionClass->newInstance(...$args);
    }

    public function withDefaultValueForProperty(string $propertyName, mixed $value): self
    {
        $this->defaultPropertyValues[$propertyName] = $value;

        return $this;
    }

    /** @return array<class-string, callable> */
    abstract protected function getMap(): array;
}
