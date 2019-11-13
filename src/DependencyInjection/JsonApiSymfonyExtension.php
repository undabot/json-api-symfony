<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Undabot\SymfonyJsonApi\Exception\EventSubscriber\ExceptionListener;

class JsonApiSymfonyExtension extends Extension
{
    public const EXCEPTION_LISTENER_PRIORITY = -128;

    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition(ExceptionListener::class);
        $tag = $definition->getTag('kernel.event_listener');
        $tag[0]['priority'] = $config['exception_listener_priority'] ?? self::EXCEPTION_LISTENER_PRIORITY;
        $definition->setTags([
            'kernel.event_listener' => $tag,
        ]);
    }
}
