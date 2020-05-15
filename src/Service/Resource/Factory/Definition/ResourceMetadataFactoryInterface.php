<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Service\Resource\Factory\Definition;

use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Metadata\ResourceMetadata;

interface ResourceMetadataFactoryInterface
{
    public function getClassMetadata(string $class): ResourceMetadata;

    public function getInstanceMetadata(ApiModel $apiModel): ResourceMetadata;
}
