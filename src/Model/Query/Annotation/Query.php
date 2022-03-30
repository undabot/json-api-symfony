<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Model\Query\Annotation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class Query
{
    public string $class;

    /**
     * @var array<string, array{
     *      propertyName: string|null,
     *      type: string,
     *      nullable: bool|null,
     * }>
     */
    public array $filters = [];

    /**
     * @var array<string, array{
     *      propertyName: string|null,
     * }>
     */
    public array $sorts = [];

    public bool $hasPagination = true;

    public bool $paginationRequired = false;
}
