<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Bridge\OpenApi\Model\JsonApi\Schema\Filter;

use Undabot\SymfonyJsonApi\Bridge\OpenApi\Contract\Schema;

class FilterSetQueryParam implements Schema
{
    /** @var string */
    private $name;

    /** @var Filter[] */
    private $filters;

    /**
     * @param Filter[] $filters
     */
    public function __construct(string $name, array $filters)
    {
        $this->name = $name;
        $this->filters = $filters;
    }

    public function toOpenApi(): array
    {
        $schema = [
            'name' => $this->name,
            'in' => 'query',
            'style' => 'deepObject',
            'explode' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [],
            ],
        ];

        /** @var Filter $filter */
        foreach ($this->filters as $filter) {
            $schema['schema']['properties'][$filter->getName()] = $filter->getSchema()->toOpenApi();
        }

        return $schema;
    }
}
