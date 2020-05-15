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
            TestClass::class => static function (TestClass $object) {
                return new TestApiModel($object->name);
            },
        ];
    }
}
