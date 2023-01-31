<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination\Creator;

use Undabot\JsonApi\Definition\Model\Request\Pagination\PaginationInterface;
use Undabot\JsonApi\Implementation\Model\Request\Pagination\OffsetBasedPagination;
use Undabot\SymfonyJsonApi\Model\Link\ResponsePaginationLink;

final class OffsetBasedPaginationLinkParametersFactory implements PaginationLinkParametersFactory
{
    public function createLinks(PaginationInterface $pagination, ?int $total): ResponsePaginationLink
    {
        return new ResponsePaginationLink(
            OffsetBasedPagination::PARAM_PAGE_OFFSET,
            $pagination->getSize(),
            $pagination->getSize() * -1,
            0,
            null === $total ? null : (int) (ceil($total / $pagination->getSize()) * $pagination->getSize() - $pagination->getSize()),
        );
    }
}
