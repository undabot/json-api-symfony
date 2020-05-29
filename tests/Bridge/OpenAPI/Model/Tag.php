<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Bridge\OpenAPI\Model;

use Undabot\SymfonyJsonApi\Model\ApiModel;
use Undabot\SymfonyJsonApi\Model\Resource\Annotation\Attribute;
use Undabot\SymfonyJsonApi\Service\Resource\Validation\Constraint\ResourceType;

/** @ResourceType(type="tag") */
class Tag implements ApiModel
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     * @Attribute
     */
    private $name;
}
