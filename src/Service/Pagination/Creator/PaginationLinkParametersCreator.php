<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Pagination\Creator;

use Undabot\JsonApi\Definition\Model\Request\Pagination\PaginationInterface;
use Undabot\SymfonyJsonApi\Model\Link\ResponsePaginationLink;

interface PaginationLinkParametersCreator
{
    public function createLinks(PaginationInterface $pagination, ?int $total): ResponsePaginationLink;
}
