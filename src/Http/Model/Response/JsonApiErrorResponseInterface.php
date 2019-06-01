<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;

interface JsonApiErrorResponseInterface extends JsonApiResponseInterface
{
    public function getErrorCollection(): ?ErrorCollectionInterface;
}
