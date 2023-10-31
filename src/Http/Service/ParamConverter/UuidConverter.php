<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Service\ParamConverter;

use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Undabot\SymfonyJsonApi\Exception\ParamConverterInvalidUuidFormatException;

/** @psalm-suppress UnusedClass */
class UuidConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();

        $attributeValue = $request->attributes->get($name);

        // Ensure the attribute value is a string
        if (!\is_string($attributeValue)) {
            throw new \InvalidArgumentException(sprintf('The attribute "%s" must be a string.', $name));
        }

        try {
            $value = Uuid::fromString($attributeValue);
        } catch (InvalidUuidStringException $ex) {
            throw new ParamConverterInvalidUuidFormatException($ex->getMessage(), (int) $ex->getCode(), $ex);
        }

        $request->attributes->set($name, $value);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return UuidInterface::class === $configuration->getClass();
    }
}
