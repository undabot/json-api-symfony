<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Request;

class GetSingleResourceRequest
{
    /** @var string */
    private $id;

    /** @var array|null */
    private $include;

    /** @var array|null */
    private $fields;

    public function __construct(string $id, ?array $include, ?array $fields)
    {
        $this->id = $id;
        $this->include = $include;
        $this->fields = $fields;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getInclude(): ?array
    {
        return $this->include;
    }

    public function getFields(): ?array
    {
        return $this->fields;
    }

    public function isIncluded(string $name): bool
    {
        if (null === $this->include) {
            return false;
        }

        return in_array($name, $this->include);
    }
}
