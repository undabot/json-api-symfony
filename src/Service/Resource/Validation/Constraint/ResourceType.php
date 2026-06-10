<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\ConstraintValidator\ResourceTypeValidator;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
class ResourceType extends Constraint
{
    public string $message = 'Invalid resource type `{{ given }}` given; `{{ expected }}` expected.';

    /**
     * @param null|string[] $groups
     */
    public function __construct(
        public ?string $type = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public static function make(string $type): self
    {
        return new self($type);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    #[\Override]
    public function validatedBy(): string
    {
        return ResourceTypeValidator::class;
    }

    #[\Override]
    public function getTargets(): array|string
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
