<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\RequestHandler;

use Undabot\JsonApi\Model\Request\UpdateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Http\Model\Response\JsonApiResponseInterface;

interface UpdateResourceRequestHandlerInterface
{
    public function handle(UpdateResourceRequestInterface $request): JsonApiResponseInterface;
}
