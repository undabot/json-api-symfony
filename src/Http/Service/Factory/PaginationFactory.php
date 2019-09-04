<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\Factory;

use InvalidArgumentException;
use Undabot\JsonApi\Model\Request\Pagination\OffsetBasedPagination;
use Undabot\JsonApi\Model\Request\Pagination\PageBasedPagination;
use Undabot\JsonApi\Model\Request\Pagination\PaginationInterface;

class PaginationFactory
{
    /**
     * @param array<string, int> $paginationParams
     */
    public function fromArray(array $paginationParams): PaginationInterface
    {
        if (true == array_key_exists(PageBasedPagination::PARAM_PAGE_SIZE, $paginationParams) &&
            true == array_key_exists(PageBasedPagination::PARAM_PAGE_NUMBER, $paginationParams)) {
            return $this->makePageBasedPagination($paginationParams);
        }

        if (true == array_key_exists(OffsetBasedPagination::PARAM_PAGE_OFFSET, $paginationParams) &&
            true == array_key_exists(OffsetBasedPagination::PARAM_PAGE_LIMIT, $paginationParams)) {
            return $this->makeOffsetBasedPagination($paginationParams);
        }

        $message = sprintf('Couldn\'t create pagination from given params: %s', json_encode($paginationParams));
        throw new InvalidArgumentException($message);
    }

    /**
     * Is given string param castable to integer?
     */
    private function isIntString(string $value): bool
    {
        if (false === is_numeric($value)) {
            return false;
        }

        return $value === (string) ((int) $value);
    }

    /**
     * @param array<string, int> $paginationParams
     */
    private function makePageBasedPagination(array $paginationParams): PageBasedPagination
    {
        $this->makeSureOnlyRequiredParamsArePresent(
            $paginationParams,
            [PageBasedPagination::PARAM_PAGE_SIZE, PageBasedPagination::PARAM_PAGE_NUMBER]
        );
        $this->makeSureParametersAreValidNonZeroIntegers($paginationParams);

        return new PageBasedPagination(
            (int) $paginationParams[PageBasedPagination::PARAM_PAGE_NUMBER],
            (int) $paginationParams[PageBasedPagination::PARAM_PAGE_SIZE]
        );
    }

    /**
     * @param array<string, int> $paginationParams
     */
    private function makeOffsetBasedPagination(array $paginationParams): OffsetBasedPagination
    {
        $this->makeSureOnlyRequiredParamsArePresent(
            $paginationParams,
            [OffsetBasedPagination::PARAM_PAGE_OFFSET, OffsetBasedPagination::PARAM_PAGE_LIMIT]
        );

        $this->makeSureParametersAreValidNonZeroIntegers([
            $paginationParams[OffsetBasedPagination::PARAM_PAGE_LIMIT],
        ]);

        $this->makeSureParametersAreValidIntegers([
            $paginationParams[OffsetBasedPagination::PARAM_PAGE_OFFSET],
        ]);

        return new OffsetBasedPagination(
            (int) $paginationParams[OffsetBasedPagination::PARAM_PAGE_OFFSET],
            (int) $paginationParams[OffsetBasedPagination::PARAM_PAGE_LIMIT]
        );
    }

    /**
     * @param int[] $paginationParams
     */
    private function makeSureParametersAreValidIntegers(array $paginationParams): void
    {
        $nonIntegerParams = array_keys(array_filter($paginationParams, function ($item) {
            if (true === is_int($item)) {
                return false;
            }

            if (true === is_string($item)) {
                return false === $this->isIntString($item);
            }

            return true;
        }));

        if (0 !== count($nonIntegerParams)) {
            $message = sprintf('Non integer params given: %s', implode(', ', $nonIntegerParams));
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param int[] $paginationParams
     */
    private function makeSureParametersAreValidNonZeroIntegers(array $paginationParams): void
    {
        $this->makeSureParametersAreValidIntegers($paginationParams);

        $zeroParameters = array_keys(array_filter($paginationParams, function ($item) {
            if (0 == (int) $item) {
                return true;
            }

            return false;
        }));

        if (0 !== count($zeroParameters)) {
            $message = sprintf('Params can\'t be zero: %s', implode(', ', $zeroParameters));
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param array<string, int> $paginationParams
     * @param string[] $requiredParams
     */
    private function makeSureOnlyRequiredParamsArePresent(array $paginationParams, array $requiredParams): void
    {
        $givenParams = array_keys($paginationParams);
        $unsupportedParams = array_diff($givenParams, $requiredParams);

        if (0 !== count($unsupportedParams)) {
            $message = sprintf('Missing required pagination parameters: %s', implode(', ', $unsupportedParams));
            throw new InvalidArgumentException($message);
        }
    }
}
