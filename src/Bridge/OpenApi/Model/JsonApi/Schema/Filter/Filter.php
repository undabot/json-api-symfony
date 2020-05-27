<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Filter;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\IntegerSchema;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\StringSchema;

final class Filter
{
    /** @var string */
    private $name;

    /** @var Schema */
    private $schema;

    /** @var bool */
    private $required;

    public function __construct(string $name, Schema $schema, bool $required = false)
    {
        $this->name = $name;
        $this->schema = $schema;
        $this->required = $required;
    }

    public static function integer(
        string $name,
        bool $required = false,
        ?string $description = null,
        ?int $example = null
    ): self {
        return new self($name, new IntegerSchema($example, $description), $required);
    }

    public static function string(
        string $name,
        bool $required = false,
        ?string $description = null,
        ?string $example = null
    ): self {
        return new self($name, new StringSchema($example, $description), $required);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSchema(): Schema
    {
        return $this->schema;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
