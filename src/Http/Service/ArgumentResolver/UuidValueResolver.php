<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ArgumentResolver;

use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Undabot\SymfonyJsonApi\Exception\InvalidUuidFormatException;

/**
 * Resolves UuidInterface controller arguments from request attributes.
 *
 * Replaces the former Sensio FrameworkExtraBundle param converter.
 */
final class UuidValueResolver implements ValueResolverInterface
{
    /**
     * @return iterable<UuidInterface>
     *
     * @throws InvalidUuidFormatException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (UuidInterface::class !== $argument->getType()) {
            return [];
        }

        $value = $request->attributes->get($argument->getName());

        if (false === \is_string($value)) {
            return [];
        }

        try {
            return [Uuid::fromString($value)];
        } catch (InvalidUuidStringException $exception) {
            throw new InvalidUuidFormatException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }
}
