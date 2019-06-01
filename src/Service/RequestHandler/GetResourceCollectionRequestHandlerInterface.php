<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\RequestHandler;

use Undabot\JsonApi\Model\Request\GetResourceCollectionRequestInterface;
use Undabot\SymfonyJsonApi\Http\Model\Response\JsonApiResponseInterface;

interface GetResourceCollectionRequestHandlerInterface
{
    public function handle(GetResourceCollectionRequestInterface $request): JsonApiResponseInterface;
}
