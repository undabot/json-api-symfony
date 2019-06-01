<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\RequestHandler;

use Undabot\JsonApi\Model\Request\CreateResourceRequestInterface;
use Undabot\SymfonyJsonApi\Http\Model\Response\JsonApiResponseInterface;

interface CreateResourceRequestHandlerInterface
{
    public function handle(CreateResourceRequestInterface $request): JsonApiResponseInterface;
}
