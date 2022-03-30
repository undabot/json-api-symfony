<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Factory;

use Assert\Assertion;
use Doctrine\Common\Annotations\Reader;
use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Exception\PaginationRequiredException;
use Undabot\SymfonyJsonApi\Model\Query\Annotation\Query;
use Undabot\SymfonyJsonApi\Model\Query\QueryFilter;
use Undabot\SymfonyJsonApi\Model\Query\QuerySort;

abstract class AbstractQueryFactory
{
    private const PAGINATION_PROPERTY_SIZE = 'size';
    private const PAGINATION_PROPERTY_OFFSET = 'offset';

    public function __construct(private Reader $reader)
    {
    }

    public function fromRequest(
        GetResourceCollectionRequestInterface $request,
        string $controllerClass
    ): object {
        $controllerReflectionClass = new \ReflectionClass($controllerClass);

        /** @var Query $queryAnnotation */
        $queryAnnotation = $this->reader->getClassAnnotation($controllerReflectionClass, Query::class);

        $this->validateProperties($queryAnnotation);

        $pagination = $request->getPagination();

        if (null === $pagination && true === $queryAnnotation->paginationRequired) {
            throw new PaginationRequiredException();
        }

        $filterSet = $request->getFilterSet();
        $filters = [];

        foreach ($queryAnnotation->filters as $filterName => $rawFilter) {
            Assertion::keyExists('type', $rawFilter);
            Assertion::string($rawFilter['type']);

            $propertyName = $rawFilter['propertyName'] ?? $filterName;

            $filters[$propertyName] = new QueryFilter(
                $filterName,
                $propertyName,
                $rawFilter['type'],
                $rawFilter['nullable'] ?? true,
                $filterSet?->getFilterValue($filterName),
            );
        }

        $sortSet = $request->getSortSet();
        $sorts = [];

        if (null !== $sortSet) {
            foreach ($queryAnnotation->sorts as $sortName => $rawSort) {
                $propertyName = $rawSort['propertyName'] ?? $sortName;

                $sorts[$propertyName] = new QuerySort(
                    $sortName,
                    $propertyName,
                    $sortSet->getSortsArray()[$sortName],
                );
            }
        }

        $queryReflectionClass = new \ReflectionClass($queryAnnotation->class);
        $queryInstance = $queryReflectionClass->newInstanceWithoutConstructor();

        foreach ($queryReflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();

            if (self::PAGINATION_PROPERTY_SIZE === $propertyName) {
                $property->setValue($queryInstance, $pagination?->getSize());

                continue;
            }

            if (self::PAGINATION_PROPERTY_OFFSET === $propertyName) {
                $property->setValue($queryInstance, $pagination?->getOffset());

                continue;
            }

            if (true === \array_key_exists($propertyName, $filters)) {
                $filter = $filters[$propertyName];
                $value = $filter->value;

                if (true === \array_key_exists($filter->type, $this->getMap())) {
                    $value = $this->getMap()[$filter->type]($value);
                }

                $property->setValue($queryInstance, $value);

                continue;
            }

            if (true === \array_key_exists($propertyName, $sorts)) {
                $sort = $sorts[$propertyName];
                $value = $sort->value;

                if (true === \array_key_exists('sort', $this->getMap())) {
                    $value = $this->getMap()['sort']($value);
                }

                $property->setValue($queryInstance, $value);

                continue;
            }

            throw new \LogicException(sprintf('Property %s has no matching filter or sort.', $propertyName));
        }

        return $queryInstance;
    }

    /** @return array<class-string, callable> */
    abstract protected function getMap(): array;

    private function validateProperties(Query $queryAnnotation)
    {
        $queryReflectionClass = new \ReflectionClass($queryAnnotation->class);

        $queryProperties = array_map(
            static fn (\ReflectionProperty $p): string => $p->getName(),
            $queryReflectionClass->getProperties(),
        );

        $expectedProperties = array_merge(
            array_keys($queryAnnotation->filters),
            $queryAnnotation->sorts,
        );

        if (true === $queryAnnotation->hasPagination) {
            $expectedProperties[] = self::PAGINATION_PROPERTY_SIZE;
            $expectedProperties[] = self::PAGINATION_PROPERTY_OFFSET;
        }

        $missingProperties = array_diff($queryProperties, $expectedProperties);

        if (false === empty($missingProperties)) {
            throw new \DomainException(sprintf('Missing properties: %s', implode(', ', $missingProperties)));
        }
    }
}
