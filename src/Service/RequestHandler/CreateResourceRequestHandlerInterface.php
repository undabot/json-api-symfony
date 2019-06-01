<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\RequestHandler;

use Undabot\JsonApi\Model\Request\CreateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Response\JsonApiResponseInterface;

interface CreateResourceRequestHandlerInterface
{
    public function handle(CreateResourceRequestInterface $request): JsonApiResponseInterface;
}
