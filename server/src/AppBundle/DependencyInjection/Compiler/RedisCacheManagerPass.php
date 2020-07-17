<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class RedisCacheManagerPass implements CompilerPassInterface
{
    /**
     * Adds all SNC PHP redis clients to the manager.
     * Is needed to ensure the appropriate application prefix is added.
     *
     * @param   ContainerBuilder    $container
     */
    public function process(ContainerBuilder $container)
    {
        $prefix     = 'snc_redis.phpredis.';
        $managerDef = $container->getDefinition('app_bundle.core.redis_cache_manager');
        foreach ($container->getDefinitions() as $id => $definition) {
            if (0 === stripos($id, $prefix)) {
                $name = str_replace($prefix, '', $id);
                $managerDef->addMethodCall('addClient', [$name, new Reference($id)]);
            }
        }
    }
}
