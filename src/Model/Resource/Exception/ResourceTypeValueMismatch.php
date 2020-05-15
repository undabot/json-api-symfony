<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Exception;

use Exception;

class ResourceTypeValueMismatch extends Exception
{
    private function __construct(string $errorMessage)
    {
        parent::__construct($errorMessage);
    }

    public static function whenUpdatingResource(
        string $resourceType,
        string $payloadReference
    ): self {
        $errorMessage = sprintf('Resource type reference mismatch: Resource type \'%s\' does not match resource payload reference \'%s\'', $resourceType, $payloadReference);

        return new self($errorMessage);
    }
}
