<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Responder;

use Undabot\SymfonyJsonApi\Model\ApiModel;

/** @psalm-immutable */
class TestApiModel implements ApiModel
{
    /** @var string */
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
