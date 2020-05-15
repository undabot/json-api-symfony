<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Error;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Undabot\JsonApi\Definition\Model\Error\ErrorInterface;
use Undabot\JsonApi\Definition\Model\Link\LinkInterface;
use Undabot\JsonApi\Definition\Model\Meta\MetaInterface;
use Undabot\JsonApi\Definition\Model\Source\SourceInterface;
use Undabot\JsonApi\Implementation\Model\Source\Source;

class ValidationViolationError implements ErrorInterface
{
    /**
     * @var ConstraintViolationInterface
     */
    private $violation;

    public function __construct(ConstraintViolationInterface $violation)
    {
        $this->violation = $violation;
    }

    public function getId(): ?string
    {
        return null;
    }

    public function getAboutLink(): ?LinkInterface
    {
        return null;
    }

    public function getStatus(): ?string
    {
        return null;
    }

    public function getCode(): ?string
    {
        return null;
    }

    public function getTitle(): ?string
    {
        return (string) $this->violation->getMessage();
    }

    public function getDetail(): ?string
    {
        $invalidValue = $this->violation->getInvalidValue();

        if (null === $invalidValue) {
            return null;
        }

        if (true === \is_string($invalidValue)) {
            return $invalidValue;
        }

        if (true === \is_object($invalidValue) && true === method_exists($invalidValue, '__toString')) {
            return (string) $invalidValue;
        }

        return null;
    }

    public function getSource(): ?SourceInterface
    {
        /** @var null|string $path */
        $path = $this->violation->getPropertyPath();
        if (null === $path) {
            return null;
        }

        // Convert Symfony attribute notation [data][attributes][attribute] to Trim.js /data/attributes/attribute
        preg_match_all('/\[(.*)\]/U', $path, $result);
        if (isset($result[1])) {
            $path = '/' . implode('/', $result[1]);
        }

        return new Source($path);
    }

    public function getMeta(): ?MetaInterface
    {
        return null;
    }
}
