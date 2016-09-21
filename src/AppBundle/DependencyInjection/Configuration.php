<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode
            ->children()
                ->arrayNode('import')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('connections')
                        ->prototype('array')
                            ->children()
                                // ->useAttributeAsKey('name')
                                ->scalarNode('dsn')->isRequired()->cannotBeEmpty()->end()
                                ->variableNode('options')->defaultValue([])->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
