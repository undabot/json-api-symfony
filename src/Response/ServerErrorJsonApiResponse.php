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

    public function __construct(string $message, string $exceptionName, array $headers = [])
    {
        parent::__construct(null, Response::HTTP_INTERNAL_SERVER_ERROR, $headers);
        $this->message = $message;
        $this->exceptionName = $exceptionName;
    }

    public function getErrorTitle(): string
    {
        return $this->exceptionName;
    }

    public static function fromException(Exception $exception): self
    {
        $message = sprintf('%s : %s', $exception->getFile(), $exception->getLine());

        return new self($message, get_class($exception));
    }
}
