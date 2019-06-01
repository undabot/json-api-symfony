<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\RequestHandler;

use Undabot\JsonApi\Model\Request\GetResourceRequestInterface;
use Undabot\SymfonyJsonApi\Http\Model\Response\JsonApiResponseInterface;

interface GetResourceRequestHandlerInterface
{
    public function handle(GetResourceRequestInterface $request): JsonApiResponseInterface;
}
