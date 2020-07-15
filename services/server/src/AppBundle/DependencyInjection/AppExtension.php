<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
        // Load bundle services
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');

        // Load bundle configuration
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Additional setup
        $this->initializeImportServices($container, $config);
    }

    /**
     * Handles creation of import services.
     */
    private function initializeImportServices(ContainerBuilder $container, array $config)
    {
        // Create the MongoDB logger service
        $logger = new Definition();
        $logger->setClass('As3\Modlr\Persister\MongoDb\Logger');
        $loggerRef = new Reference('logger');
        $logger->setArguments([$loggerRef, 'import query']);
        $container->setDefinition('app_bundle.import.logger', $logger);
        $loggerRef = new Reference('app_bundle.import.logger');

        // Create the MongoDB configuration
        $configuration = new Definition();
        $configuration->setClass('Doctrine\MongoDB\Configuration');
        $configuration->addMethodCall('setLoggerCallable', [[new Reference($loggerRef), 'logQuery']]);
        $container->setDefinition('app_bundle.import.configuration', $configuration);
        $configurationRef = new Reference('app_bundle.import.configuration');

        // Create the MongoDB connections
        foreach ($config['import']['connections'] as $key => $parameters) {
            $name = sprintf('app_bundle.import.connection.%s', $key);
            $def = new Definition();
            $def->setClass('Doctrine\MongoDB\Connection');
            $def->setArguments([$parameters['dsn'], $parameters['options'], $configurationRef]);
            $container->setDefinition($name, $def);

            // Inject the connnection into the related import source.
            $ref = new Reference($name);
            $source = $container->getDefinition(sprintf('app_bundle.import.source.mongo.%s', $key));
            $source->setArguments([$ref]);
        }
    }
}
