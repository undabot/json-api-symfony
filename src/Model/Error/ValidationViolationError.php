<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Error;

use BadMethodCallException;
use Symfony\Component\Validator\ConstraintViolation;
use Undabot\JsonApi\Model\Error\ErrorInterface;
use Undabot\JsonApi\Model\Link\LinkInterface;
use Undabot\JsonApi\Model\Meta\Meta;
use Undabot\JsonApi\Model\Source\Source;

class ValidationViolationError implements ErrorInterface
{
    /**
     * @var ConstraintViolation
     */
    private $violation;

    public function __construct(ConstraintViolation $violation)
    {
        $this->violation = $violation;
    }

    public static function fromViolation(ConstraintViolation $violation): self
    {
        return new self($violation);
    }

    public function getId(): ?string
    {
        throw new BadMethodCallException('Not allowed.');
    }

    public function getAboutLink(): LinkInterface
    {
        throw new BadMethodCallException('Not allowed.');
    }

    public function getStatus(): ?string
    {
        throw new BadMethodCallException('Not allowed.');
    }

    public function getCode(): ?string
    {
        throw new BadMethodCallException('Not allowed.');
    }

    public function getTitle(): ?string
    {
        return $this->violation->getMessage();
    }

    public function getDetail(): ?string
    {
        return $this->violation->getCause();
    }

    public function getSource(): Source
    {
        throw new BadMethodCallException('Not allowed.');
    }

    public function getMeta(): Meta
    {
        throw new BadMethodCallException('Not allowed.');
    }
}
