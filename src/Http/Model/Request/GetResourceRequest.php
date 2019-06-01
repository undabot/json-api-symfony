<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Request;

use Undabot\JsonApi\Model\Request\GetResourceRequestInterface;

class GetResourceRequest implements GetResourceRequestInterface
{
    /** @var string */
    private $id;

    /** @var array|null */
    private $include;

    /** @var array|null */
    private $sparseFieldset;

    public function __construct(string $id, ?array $include, ?array $sparseFieldset)
    {
        $this->id = $id;
        $this->include = $include;
        $this->sparseFieldset = $sparseFieldset;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getInclude(): ?array
    {
        return $this->include;
    }

    public function isIncluded(string $name): bool
    {
        if (null === $this->include) {
            return false;
        }

        return in_array($name, $this->include);
    }

    public function getSparseFieldset(): ?array
    {
        return $this->sparseFieldset;
    }
}
