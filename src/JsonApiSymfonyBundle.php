<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Undabot\SymfonyJsonApi\DependencyInjection\Compiler\ApiGeneratorPass;

class JsonApiSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ApiGeneratorPass());
    }
}
