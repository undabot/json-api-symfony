<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\Tests\Unit\Responder;

use Undabot\SymfonyJsonApi\Http\Service\Responder\AbstractResponder;

class TestResponder extends AbstractResponder
{
    /**
     * @return array<string, callable>
     */
    protected function getMap(): array
    {
        return [
            \stdClass::class => static function () {
                return new \stdClass();
            },
        ];
    }
}
