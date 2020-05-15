<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Resource\Exception;

use Exception;

class ResourceIdValueMismatch extends Exception
{
    private function __construct(string $errorMessage)
    {
        parent::__construct($errorMessage);
    }

    public static function whenUpdatingResource(
        string $resourceType,
        string $URIReference,
        string $payloadReference
    ): self {
        $errorMessage = sprintf('Resource type \'%s\' reference mismatch: URI reference \'%s\' does not match resource payload reference \'%s\'', $resourceType, $URIReference, $payloadReference);

        return new self($errorMessage);
    }
}
