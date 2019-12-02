<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Request;

use Undabot\JsonApi\Definition\Exception\Request\UnsupportedFilterAttributeGivenException;
use Undabot\JsonApi\Definition\Exception\Request\UnsupportedIncludeValuesGivenException;
use Undabot\JsonApi\Definition\Exception\Request\UnsupportedPaginationRequestedException;
use Undabot\JsonApi\Definition\Exception\Request\UnsupportedSortRequestedException;
use Undabot\JsonApi\Definition\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\JsonApi\Definition\Model\Request\Pagination\PaginationInterface;
use Undabot\JsonApi\Implementation\Model\Request\Filter\FilterSet;
use Undabot\JsonApi\Implementation\Model\Request\Sort\SortSet;

class GetResourceCollectionRequest implements GetResourceCollectionRequestInterface
{
    public const PAGINATION_KEY = 'page';
    public const SORT_KEY = 'sort';
    public const FILTER_KEY = 'filter';
    public const INCLUDE_KEY = 'include';
    public const FIELDS_KEY = 'fields';

    /** @var null|PaginationInterface */
    private $pagination;

    /** @var null|FilterSet */
    private $filterSet;

    /** @var null|SortSet */
    private $sortSet;

    /** @var null|array */
    private $includes;

    /** @var null|array */
    private $fields;

    public function __construct(
        ?PaginationInterface $pagination,
        ?FilterSet $filterSet,
        ?SortSet $sortSet,
        ?array $include,
        ?array $fields
    ) {
        $this->pagination = $pagination;
        $this->filterSet = $filterSet;
        $this->sortSet = $sortSet;
        $this->includes = $include;
        $this->fields = $fields;
    }

    public function getPagination(): ?PaginationInterface
    {
        return $this->pagination;
    }

    public function getFilterSet(): ?FilterSet
    {
        return $this->filterSet;
    }

    public function getSortSet(): ?SortSet
    {
        return $this->sortSet;
    }

    public function getIncludes(): ?array
    {
        return $this->includes;
    }

    public function getSparseFieldset(): ?array
    {
        return $this->fields;
    }

    public function isIncluded(string $name): bool
    {
        if (null === $this->includes) {
            return false;
        }

        return \in_array($name, $this->includes, true);
    }

    /**
     * @throws UnsupportedPaginationRequestedException
     */
    public function disablePagination(): GetResourceCollectionRequestInterface
    {
        if (null !== $this->pagination) {
            throw new UnsupportedPaginationRequestedException();
        }

        return $this;
    }

    /**
     * @throws UnsupportedFilterAttributeGivenException
     */
    public function allowFilters(array $allowedFilters): GetResourceCollectionRequestInterface
    {
        $filters = null === $this->filterSet
            ? []
            : $filters = $this->filterSet->getFilterNames();

        $unsupportedFilters = array_diff($filters, $allowedFilters);
        if (0 !== \count($unsupportedFilters)) {
            throw new UnsupportedFilterAttributeGivenException($unsupportedFilters);
        }

        return $this;
    }

    /**
     * @param string[] $allowedIncludes
     *
     * @throws UnsupportedIncludeValuesGivenException
     */
    public function allowIncluded(array $allowedIncludes): GetResourceCollectionRequestInterface
    {
        $unsupportedIncludes = array_diff($this->includes ?: [], $allowedIncludes);
        if (0 !== \count($unsupportedIncludes)) {
            throw new UnsupportedIncludeValuesGivenException($unsupportedIncludes);
        }

        return $this;
    }

    /**
     * @param string[] $allowedSorts
     *
     * @throws UnsupportedSortRequestedException
     */
    public function allowSorting(array $allowedSorts): GetResourceCollectionRequestInterface
    {
        $sorts = null === $this->sortSet
            ? []
            : array_keys($this->sortSet->getSortsArray());

        $unsupportedSorts = array_diff($sorts ?: [], $allowedSorts);
        if (0 !== \count($unsupportedSorts)) {
            throw new UnsupportedSortRequestedException($unsupportedSorts);
        }

        return $this;
    }
}
