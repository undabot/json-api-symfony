<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\RequestHandler;

use Undabot\JsonApi\Model\Request\UpdateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Response\JsonApiResponseInterface;

interface UpdateResourceRequestHandlerInterface
{
    public function handle(UpdateResourceRequestInterface $request): JsonApiResponseInterface;
}
