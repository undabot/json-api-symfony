<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Undabot\JsonApi\Definition\Encoding\DocumentToPhpArrayEncoderInterface;
use Undabot\SymfonyJsonApi\Exception\EventSubscriber\ExceptionListener;

class JsonApiSymfonyExtension extends Extension
{
    public const EXCEPTION_LISTENER_PRIORITY = -128;

    /**
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = new Definition(ExceptionListener::class, [
            new Reference(DocumentToPhpArrayEncoderInterface::class),
        ]);
        $definition->setTags([
            'kernel.event_listener' => [
                [
                    'event' => 'kernel.exception',
                    'priority' => $config['exception_listener_priority'] ?? self::EXCEPTION_LISTENER_PRIORITY,
                ],
            ],
        ]);
        $container->setDefinition('json_api_symfony.exception_listener', $definition);
    }
}
