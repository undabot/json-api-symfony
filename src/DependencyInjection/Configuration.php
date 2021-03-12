<?php

declare(strict_types=1);

namespace Undabot\SymfonyJsonApi\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('json_api_symfony');

        $treeBuilder
            ->getRootNode()
            ->children()
            ->integerNode('exception_listener_priority')
            ->min(-255)
            ->max(255)
            ->end()
            ->end();

        return $treeBuilder;
    }
}
