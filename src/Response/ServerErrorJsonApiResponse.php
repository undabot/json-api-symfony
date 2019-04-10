<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Response;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ServerErrorJsonApiResponse extends AbstractErrorJsonApiResponse
{
    /**
     * @var string
     * Exception name that is used for building the Error title
     */
    private $exceptionName;

    public function __construct(string $message, string $exceptionName, array $headers = [], ?int $statusCode = null)
    {
        $responseStatus = $statusCode ?? Response::HTTP_INTERNAL_SERVER_ERROR;
        parent::__construct(null, $responseStatus, $headers);
        $this->message = $message;
        $this->exceptionName = $exceptionName;
    }

    public function getErrorTitle(): string
    {
        return $this->exceptionName;
    }

    public static function fromException(Exception $exception): self
    {
        $message = self::buildErrorMessageFromException($exception);
        $responseStatus = $exception->getCode() ?? Response::HTTP_INTERNAL_SERVER_ERROR;

        return new self($message, get_class($exception), [], (int) $responseStatus);
    }

    private static function buildErrorMessageFromException(Exception $exception)
    {
        $message = null === $exception->getCode() ? sprintf('%s : %s', $exception->getFile(), $exception->getLine())
            : $exception->getMessage();

        return $message;
    }
}
