<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Factory;

use Undabot\JsonApi\Model\Resource\ResourceIdentifierInterface;
use Undabot\JsonApi\Model\Resource\ResourceInterface;

interface EntityToResourceFactoryInterface
{
    public function create($entity): ResourceInterface;

    public function createIdentifier($entity): ResourceIdentifierInterface;
}
