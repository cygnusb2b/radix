<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AppExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['import']['connections'] as $key => $parameters) {
            $name = sprintf('app_bundle.import.connection.%s', $key);
            $def = new Definition();
            $def->setClass('Doctrine\MongoDB\Connection');
            $def->setArguments([$parameters['dsn'], $parameters['options']]);
            $container->setDefinition($name, $def);

            foreach ($container->findTaggedServiceIds(sprintf('import.connection.%s', $key)) as $importer) {
                $ref = new Reference($name);
                $importer->setArguments([$ref]);
            }
        }
    }
}
