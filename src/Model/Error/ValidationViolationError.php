<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Error;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Undabot\JsonApi\Model\Error\ErrorInterface;
use Undabot\JsonApi\Model\Link\LinkInterface;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Source\Source;

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
        return $this->violation->getMessage();
    }

    public function getDetail(): ?string
    {
        if (null === $this->violation->getInvalidValue()) {
            return null;
        }

        return (string) $this->violation->getInvalidValue();
    }

    public function getSource(): ?Source
    {
        /** @var string|null $path */
        $path = $this->violation->getPropertyPath();
        if (null === $path) {
            return null;
        }

        return new Source($path);
    }

    public function getMeta(): ?Meta
    {
        return null;
    }
}
