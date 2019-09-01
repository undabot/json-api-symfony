<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Request;

use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Model\Request\Filter\FilterSet;
use Undabot\JsonApi\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\JsonApi\Model\Request\Pagination\PaginationInterface;
use Undabot\JsonApi\Model\Request\Sort\SortSet;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedFilterAttributeGivenException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedIncludeValuesGivenException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedPaginationRequestedException;
use Undabot\SymfonyJsonApi\Http\Exception\Request\UnsupportedSortRequestedException;
use Undabot\SymfonyJsonApi\Http\Service\Factory\PaginationFactory;

class GetResourceCollectionRequest implements GetResourceCollectionRequestInterface
{
    const PAGINATION_KEY = 'page';
    const SORT_KEY = 'sort';
    const FILTER_KEY = 'filter';
    const INCLUDE_KEY = 'include';
    const FIELDS_KEY = 'fields';

    /** @var PaginationInterface|null */
    private $pagination;

    /** @var FilterSet|null */
    private $filterSet;

    /** @var SortSet|null */
    private $sortSet;

    /** @var array|null */
    private $includes;

    /** @var array|null */
    private $fields;

    public static function createFromRequest(Request $request): self
    {
        $sortSet = null;
        $filterSet = null;
        $pagination = null;
        $include = null;
        $fields = null;

        $sortParams = $request->get(self::SORT_KEY, null);
        if (null !== $sortParams) {
            $sortSet = SortSet::make($sortParams);
        }

        $paginationParams = $request->query->get(self::PAGINATION_KEY, null);
        if (null !== $paginationParams) {
            $paginationFactory = new PaginationFactory();
            $pagination = $paginationFactory->makeFromArray($paginationParams);
        }

        $filterParams = $request->query->get(self::FILTER_KEY, null);
        if (null !== $filterParams) {
            $filterSet = FilterSet::createFromArray($filterParams);
        }

        $sortString = $request->query->get(self::SORT_KEY, null);
        if (null !== $sortString) {
            $sortSet = SortSet::make($sortString);
        }

        $includeString = $request->query->get(self::INCLUDE_KEY, null);
        if (null !== $includeString) {
            $include = explode(',', $includeString);
        }

        $fields = $request->query->get(self::FIELDS_KEY, null);

        return new self(
            $pagination,
            $filterSet,
            $sortSet,
            $include,
            $fields
        );
    }

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

        return in_array($name, $this->includes);
    }

    /**
     * @throws UnsupportedPaginationRequestedException
     */
    public function disablePagination(): self
    {
        if (null !== $this->pagination) {
            throw new UnsupportedPaginationRequestedException();
        }

        return $this;
    }

    /**
     * @throws UnsupportedFilterAttributeGivenException
     */
    public function allowFilters(array $allowedFilters): self
    {
        $filters = null === $this->filterSet
            ? []
            : $filters = $this->filterSet->getFilterNames();

        $unsupportedFilters = array_diff($filters, $allowedFilters);
        if (0 !== count($unsupportedFilters)) {
            throw new UnsupportedFilterAttributeGivenException($unsupportedFilters);
        }

        return $this;
    }

    /**
     * @param string[] $includes
     * @throws UnsupportedIncludeValuesGivenException
     */
    public function allowIncluded(array $allowedIncludes): self
    {
        $unsupportedIncludes = array_diff($this->includes ?: [], $allowedIncludes);
        if (0 !== count($unsupportedIncludes)) {
            throw new UnsupportedIncludeValuesGivenException($unsupportedIncludes);
        }

        return $this;
    }

    /**
     * @param string[] $sorts
     * @throws UnsupportedSortRequestedException
     */
    public function allowSorting(array $allowedSorts): self
    {
        $sorts = null === $this->sortSet
            ? []
            : array_keys($this->sortSet->getSortsArray());

        $unsupportedSorts = array_diff($sorts ?: [], $allowedSorts);
        if (0 !== count($unsupportedSorts)) {
            throw new UnsupportedSortRequestedException($unsupportedSorts);
        }

        return $this;
    }
}
