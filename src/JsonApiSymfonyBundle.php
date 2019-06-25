<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Undabot\SymfonyJsonApi\CompilerPass\ModelEncoderMapRegistrationCompilerPass;

class JsonApiSymfonyBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ModelEncoderMapRegistrationCompilerPass());
    }
}
