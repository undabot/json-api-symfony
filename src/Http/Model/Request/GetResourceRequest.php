<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Http\Model\Request;

use Undabot\JsonApi\Definition\Exception\Request\UnsupportedIncludeValuesGivenException;
use Undabot\JsonApi\Definition\Exception\Request\UnsupportedSparseFieldsetRequestedException;
use Undabot\JsonApi\Definition\Model\Request\GetResourceRequestInterface;

class GetResourceRequest implements GetResourceRequestInterface
{
    public const INCLUDE_KEY = 'include';
    public const FIELDS_KEY = 'fields';

    /** @var string */
    private $id;

    /** @var null|array */
    private $include;

    /** @var null|array */
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

    public function getIncludes(): ?array
    {
        return $this->include;
    }

    public function isIncluded(string $name): bool
    {
        if (null === $this->include) {
            return false;
        }

        return \in_array($name, $this->include, true);
    }

    public function getSparseFieldset(): ?array
    {
        return $this->sparseFieldset;
    }

    /**
     * @throws UnsupportedIncludeValuesGivenException
     */
    public function allowIncluded(array $includes): GetResourceRequestInterface
    {
        $unsupportedIncludes = array_diff($this->include ?: [], $includes);
        if (0 !== \count($unsupportedIncludes)) {
            throw new UnsupportedIncludeValuesGivenException($unsupportedIncludes);
        }

        return new self(
            $this->id,
            $includes,
            $this->sparseFieldset
        );
    }

    /**
     * @throws UnsupportedSparseFieldsetRequestedException
     */
    public function allowFields(array $fields): GetResourceRequestInterface
    {
        $unsupportedFields = array_diff($this->sparseFieldset ?: [], $fields);
        if (0 !== \count($unsupportedFields)) {
            throw new UnsupportedSparseFieldsetRequestedException($unsupportedFields);
        }

        return new self(
            $this->id,
            $this->sparseFieldset,
            $fields
        );
    }
}
