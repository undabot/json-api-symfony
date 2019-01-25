<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Undabot\JsonApi\Model\Error\ErrorCollectionInterface;

interface JsonApiErrorResponseInterface extends JsonApiResponseInterface
{
    public function getErrorCollection(): ?ErrorCollectionInterface;
}
