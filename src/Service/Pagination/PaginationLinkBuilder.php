<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination;

use Symfony\Component\HttpFoundation\Request;
use Undabot\JsonApi\Definition\Model\Link\LinkCollectionInterface;
use Undabot\JsonApi\Definition\Model\Link\LinkNamesEnum;
use Undabot\JsonApi\Implementation\Factory\PaginationFactory;
use Undabot\JsonApi\Implementation\Model\Link\Link;
use Undabot\JsonApi\Implementation\Model\Link\LinkCollection;
use Undabot\JsonApi\Implementation\Model\Link\LinkUrl;
use Undabot\JsonApi\Implementation\Model\Request\Pagination\OffsetBasedPagination;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;
use Undabot\SymfonyJsonApi\Service\Pagination\Creator\OffsetBasedPaginationLinkParametersCreator;
use Undabot\SymfonyJsonApi\Service\Pagination\Creator\PageBasedPaginationLinkParametersCreator;

final class PaginationLinkBuilder
{
    public function createLinks(Request $request, ResourceCollectionResponse $response): ?LinkCollectionInterface
    {
        $pagination = $request->query->has(GetResourceCollectionRequest::PAGINATION_KEY)
            ? (new PaginationFactory())->fromArray($request->query->all()[GetResourceCollectionRequest::PAGINATION_KEY]
                ?? [])
            : null;
        $links = $response->getLinks();
        if (null === $pagination) {
            return $links;
        }
        $queryParams = $request->query->all();
        $total = null;
        if (null !== $response->getMeta()) {
            $total = $response->getMeta()->getData()['total'] ?? null;
        }
        $responsePaginationLink = (true === ($pagination instanceof OffsetBasedPagination))
            ? (new OffsetBasedPaginationLinkParametersCreator())->createLinks(
                $pagination,
                $total,
            )
            : (new PageBasedPaginationLinkParametersCreator())->createLinks(
                $pagination,
                $total,
            );
        $queryParamsFirst = $queryParams;
        $queryParamsFirst[GetResourceCollectionRequest::PAGINATION_KEY][$responsePaginationLink->paginationPageKey]
            = $responsePaginationLink->firstPageKey;
        $paginationLinks = [$this->buildLink(LinkNamesEnum::LINK_NAME_PAGINATION_FIRST, $request, $queryParamsFirst)];
        if (null !== $responsePaginationLink->lastPageKey) {
            $queryParamsLast = $queryParams;
            $queryParamsLast[GetResourceCollectionRequest::PAGINATION_KEY][$responsePaginationLink->paginationPageKey]
                = $responsePaginationLink->lastPageKey;
            $paginationLinks[] = $this->buildLink(LinkNamesEnum::LINK_NAME_PAGINATION_LAST, $request, $queryParamsLast);
        }
        if (0 !== $pagination->getOffset()) {
            $queryParamsPrev = $queryParams;
            $queryParamsPrev[GetResourceCollectionRequest::PAGINATION_KEY][$responsePaginationLink->paginationPageKey] += $responsePaginationLink->previousSet;
            $paginationLinks[] = $this->buildLink(LinkNamesEnum::LINK_NAME_PAGINATION_PREV, $request, $queryParamsPrev);
        }
        if (null !== $total && ($pagination->getOffset() + $pagination->getSize()) < $total) {
            $queryParamsNext = $queryParams;
            $queryParamsNext[GetResourceCollectionRequest::PAGINATION_KEY][$responsePaginationLink->paginationPageKey] += $responsePaginationLink->nextSet;
            $paginationLinks[] = $this->buildLink(LinkNamesEnum::LINK_NAME_PAGINATION_NEXT, $request, $queryParamsNext);
        }

        return new LinkCollection(array_merge($paginationLinks, null === $links ? [] : $links->getLinks()));
    }

    /** @param array<string,string> $queryParams */
    private function buildLink(string $linkName, Request $request, array $queryParams): Link
    {
        return new Link(
            $linkName,
            new LinkUrl($request->getPathInfo() . '?' . urldecode(http_build_query($queryParams))),
        );
    }
}
