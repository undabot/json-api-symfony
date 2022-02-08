<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class JsonApiHttpResponse extends Response
{
    private const CONTENT_TYPE = 'application/vnd.api+json';

    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     */
    public static function validationError(array $data): self
    {
        return self::makeError($data, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function notFound(): self
    {
        return new self(
            null,
            Response::HTTP_NOT_FOUND,
            [
                'Content-Type' => self::CONTENT_TYPE,
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     */
    public static function badRequest(array $data): self
    {
        return self::makeError($data, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     */
    public static function forbidden(array $data): self
    {
        return self::makeError($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     */
    public static function unauthorized(array $data): self
    {
        return self::makeError($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     */
    public static function serverError(array $data): self
    {
        return self::makeError($data, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     */
    public static function fromSymfonyHttpException(array $data, HttpExceptionInterface $exception): self
    {
        return self::makeError($data, $exception->getStatusCode());
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws \JsonException
     */
    private static function makeError(array $data, int $statusCode): self
    {
        $content = json_encode($data, JSON_THROW_ON_ERROR);

        return new self(
            $content ?: null,
            $statusCode,
            [
                'Content-Type' => self::CONTENT_TYPE,
            ]
        );
    }
}
