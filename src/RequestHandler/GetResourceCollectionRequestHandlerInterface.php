<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\RequestHandler;

use Undabot\JsonApi\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Response\JsonApiResponseInterface;

interface GetResourceCollectionRequestHandlerInterface
{
    public function handle(GetResourceCollectionRequestInterface $request): JsonApiResponseInterface;
}
