<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\RequestHandler;

use Undabot\JsonApi\Model\Request\GetResourceRequestInterface;
use Undabot\SymfonyJsonApi\Response\JsonApiResponseInterface;

interface GetResourceRequestHandlerInterface
{
    public function handle(GetResourceRequestInterface $request): JsonApiResponseInterface;
}
