<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination\Creator;

use Undabot\JsonApi\Definition\Model\Request\Pagination\PaginationInterface;
use Undabot\JsonApi\Implementation\Model\Request\Pagination\PageBasedPagination;
use Undabot\SymfonyJsonApi\Model\Link\ResponsePaginationLink;

final class PageBasedPaginationLinkParametersFactory implements PaginationLinkParametersFactory
{
    public function createLinks(PaginationInterface $pagination, ?int $total): ResponsePaginationLink
    {
        return new ResponsePaginationLink(
            PageBasedPagination::PARAM_PAGE_NUMBER,
            1,
            -1,
            1,
            null === $total ? null : (int) ceil($total / $pagination->getSize()),
        );
    }
}
