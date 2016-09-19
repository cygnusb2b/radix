<?php

namespace AppBundle\Core;

use As3\Modlr\Metadata\EntityMetadata;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Metadata\Events;
use As3\Modlr\Metadata\Events\MetadataArguments;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Prefixes the modlr database name with the active apps's account and application keys.
 *
 * @author Josh Worden <solocommand@gmail.com>
 */
class PersistencePrefixer implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param   ContainerInterface     $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            Events::onMetadataLoad,
            Events::onMetadataCacheLoad,
        ];
    }

    /**
     * Injects account/application suffix for database names
     *
     * @param   MetadataArguments     $args
     */
    public function onMetadataCacheLoad(MetadataArguments $args)
    {
        $this->setDatabaseFor($args->getMetadata());
    }

    /**
     * Injects account/application suffix for database names
     *
     * @param   MetadataArguments     $args
     */
    public function onMetadataLoad(MetadataArguments $args)
    {
        $this->setDatabaseFor($args->getMetadata());
    }

    /**
     * @return  AccountManager
     */
    private function getManager()
    {
        return $this->container->get('app_bundle.core.account_manager');
    }

    /**
     * @param   EntityMetadata  $metadata
     */
    private function setDatabaseFor(EntityMetadata $metadata)
    {
        $dbName  = 'radix';
        $manager = $this->getManager();
        if (0 !== stripos($metadata->type, 'core-')) {
            // Application specific model.
            if (false === $manager->shouldAllowDbOperations()) {
                throw new \RuntimeException('No application has been defined. Database operations halted.');
            }
            if (true === $manager->hasApplication()) {
                $dbName = sprintf('%s_%s', $dbName, $manager->getDatabaseSuffix());
            }

        }
        $metadata->persistence->db = $dbName;
    }
}
