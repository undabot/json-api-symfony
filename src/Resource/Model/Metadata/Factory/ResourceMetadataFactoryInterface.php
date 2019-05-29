<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Resource\Model\Metadata\Factory;

use Undabot\SymfonyJsonApi\Resource\Model\Metadata\ResourceMetadata;

interface ResourceMetadataFactoryInterface
{
    public function getClassMetadata(string $class): ResourceMetadata;

    public function getResourceMetadata($resource): ResourceMetadata;
}
