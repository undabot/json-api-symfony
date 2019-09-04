<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\MappedModelEncoder;
use Undabot\SymfonyJsonApi\Http\Service\ModelEncoder\ModelEncoderMapInterface;

class ModelEncoderMapRegistrationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $tag = 'ub.jsonapi.model_encoder_map';
        $container->registerForAutoconfiguration(ModelEncoderMapInterface::class)->addTag($tag);

        $mappedEncoderDefinition = $container->findDefinition(MappedModelEncoder::class);

        foreach ($container->findTaggedServiceIds($tag) as $id => $tags) {
            $mappedEncoderDefinition->addMethodCall('addEncodingMap', [new Reference($id)]);
        }
    }
}
