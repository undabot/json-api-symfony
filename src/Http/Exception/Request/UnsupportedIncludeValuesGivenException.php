<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Exception\Request;

use Throwable;

class UnsupportedIncludeValuesGivenException extends JsonApiRequestException
{
    public function __construct(array $unsupportedIncludes, ?$message = null, $code = 0, Throwable $previous = null)
    {
        if (null === $message) {
            $message = sprintf(
                'Unsupported include query params given: `%s`',
                implode(', ', $unsupportedIncludes)
            );
        }

        parent::__construct($message, $code, $previous);
    }
}
