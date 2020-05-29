<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Undabot\SymfonyJsonApi\Bridge\OpenApi\OpenApiGenerator;

class ApiGeneratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(OpenApiGenerator::class)) {
            return;
        }

        $definition = $container->findDefinition(OpenApiGenerator::class);

        $taggedServices = $container->findTaggedServiceIds('app.open_api.definition');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addDefinition', [new Reference($id)]);
        }

        $taggedServices = $container->findTaggedServiceIds('app.open_api.resource');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addResource', [new Reference($id)]);
        }
    }
}
