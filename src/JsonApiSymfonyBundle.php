<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Undabot\SymfonyJsonApi\DependencyInjection\Compiler\ApiGeneratorPass;

class JsonApiSymfonyBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ApiGeneratorPass());
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('DependencyInjection/config/services.yaml');
    }
}
