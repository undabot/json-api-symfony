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
use Undabot\JsonApi\Implementation\Model\Request\Pagination\PageBasedPagination;
use Undabot\SymfonyJsonApi\Http\Model\Request\GetResourceCollectionRequest;
use Undabot\SymfonyJsonApi\Http\Model\Response\ResourceCollectionResponse;

class PaginationLinkBuilder
{
    public function createLinks(Request $request, ResourceCollectionResponse $response): LinkCollectionInterface
    {
        $pagination = $request->query->has(GetResourceCollectionRequest::PAGINATION_KEY)
            ? (new PaginationFactory())->fromArray($request->query->get(GetResourceCollectionRequest::PAGINATION_KEY))
            : null;
        $links = $response->getLinks();
        if (null !== $pagination) {
            $queryParams = $request->query->all();
            $total = null;
            if (null !== $response->getMeta()) {
                $total = $response->getMeta()->getData()['total'] ?? null;
            }
            $key = PageBasedPagination::PARAM_PAGE_NUMBER;
            $nextSet = 1;
            $prevSet = -1;
            $firstPageKey = 1;
            $lastPageKey = null;
            if (null !== $total) {
                $lastPageKey = ceil($total / $pagination->getSize());
            }
            if (true === ($pagination instanceof OffsetBasedPagination)) {
                $key = OffsetBasedPagination::PARAM_PAGE_OFFSET;
                $nextSet = $pagination->getSize();
                $prevSet = $pagination->getSize() * -1;
                $firstPageKey = 0;
                if (null !== $lastPageKey) {
                    $lastPageKey = $lastPageKey * $pagination->getSize() - $pagination->getSize();
                }
            }
            $queryParamsFirst = $queryParams;
            $queryParamsFirst[GetResourceCollectionRequest::PAGINATION_KEY][$key] = $firstPageKey;
            $lastLink = null;
            $prevLink = null;
            $nextLink = null;
            $paginationLinks = [
                new Link(
                    LinkNamesEnum::LINK_NAME_PAGINATION_FIRST,
                    new LinkUrl($request->getPathInfo() . '?' . urldecode(http_build_query($queryParamsFirst)))
                )
            ];
            if (null !== $lastPageKey) {
                $queryParamsLast = $queryParams;
                $queryParamsLast[GetResourceCollectionRequest::PAGINATION_KEY][$key] = $lastPageKey;
                $lastLink = new Link(
                    LinkNamesEnum::LINK_NAME_PAGINATION_LAST,
                    new LinkUrl($request->getPathInfo() . '?' . urldecode(http_build_query($queryParamsLast)))
                );
                $paginationLinks[] = $lastLink;
            }
            if (0 !== $pagination->getOffset()) {
                $queryParamsPrev = $queryParams;
                $queryParamsPrev[GetResourceCollectionRequest::PAGINATION_KEY][$key] += $prevSet;
                $prevLink = new Link(
                    LinkNamesEnum::LINK_NAME_PAGINATION_PREV,
                    new LinkUrl($request->getPathInfo() . '?' . urldecode(http_build_query($queryParamsPrev)))
                );
                $paginationLinks[] = $prevLink;
            }
            if (null !== $total && ($pagination->getOffset() + $pagination->getSize()) < $total) {
                $queryParamsNext = $queryParams;
                $queryParamsNext[GetResourceCollectionRequest::PAGINATION_KEY][$key] += $nextSet;
                $nextLink = new Link(
                    LinkNamesEnum::LINK_NAME_PAGINATION_NEXT,
                    new LinkUrl($request->getPathInfo() . '?' . urldecode(http_build_query($queryParamsNext)))
                );
                $paginationLinks[] = $nextLink;
            }

            $links = new LinkCollection(array_merge(
                    $paginationLinks,
                    null === $response->getLinks() ? [] : $response->getLinks()->getLinks())
            );
        }

        return $links ?? new LinkCollection([]);
    }
}
