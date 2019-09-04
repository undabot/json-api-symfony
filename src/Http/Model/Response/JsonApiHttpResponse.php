<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Response;

use Symfony\Component\HttpFoundation\Response;

class JsonApiHttpResponse extends Response
{
    private const CONTENT_TYPE = 'application/vnd.api+json';

    /**
     * @param array<string, mixed> $data
     */
    public static function validationError(array $data): self
    {
        return new self(
            json_encode($data),
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
     */
    public static function badRequest(array $data): self
    {
        return new self(
            json_encode($data),
            Response::HTTP_BAD_REQUEST,
            [
                'Content-Type' => self::CONTENT_TYPE,
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function serverError(array $data): self
    {
        return new self(
            json_encode($data),
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [
                'Content-Type' => self::CONTENT_TYPE,
            ]
        );
    }
}
