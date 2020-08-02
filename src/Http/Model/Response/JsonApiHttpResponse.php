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
        $content = json_encode($data, JSON_THROW_ON_ERROR);

        return new self(
            $content ?: null,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            [
                'Content-Type' => self::CONTENT_TYPE,
            ]
        );
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
        $content = json_encode($data, JSON_THROW_ON_ERROR);

        return new self(
            $content ?: null,
            Response::HTTP_BAD_REQUEST,
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
    public static function forbidden(array $data): self
    {
        $content = json_encode($data, JSON_THROW_ON_ERROR);

        return new self(
            $content ?: null,
            Response::HTTP_FORBIDDEN,
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
    public static function unauthorized(array $data): self
    {
        $content = json_encode($data, JSON_THROW_ON_ERROR);

        return new self(
            $content ?: null,
            Response::HTTP_UNAUTHORIZED,
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
    public static function serverError(array $data): self
    {
        $content = json_encode($data, JSON_THROW_ON_ERROR);

        return new self(
            $content ?: null,
            Response::HTTP_INTERNAL_SERVER_ERROR,
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
    public static function fromSymfonyHttpException(array $data, HttpExceptionInterface $exception): self
    {
        $content = json_encode($data, JSON_THROW_ON_ERROR);

        return new self(
            $content ?: null,
            $exception->getStatusCode(),
            [
                'Content-Type' => self::CONTENT_TYPE,
            ]
        );
    }
}
