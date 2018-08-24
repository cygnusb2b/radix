<?php

namespace AppBundle\Core;

use As3\Modlr\Metadata\EntityMetadata;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Metadata\Events;
use As3\Modlr\Metadata\Events\MetadataArguments;

/**
 * Prefixes the modlr database name with the active apps's account and application keys.
 *
 * @author Josh Worden <solocommand@gmail.com>
 */
class PersistencePrefixer implements EventSubscriberInterface
{
    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @param   AccountManager      $manager
     */
    public function __construct(AccountManager $manager)
    {
        $this->manager = $manager;
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
     * @param   EntityMetadata  $metadata
     */
    private function setDatabaseFor(EntityMetadata $metadata)
    {
        $dbName  = 'radix';
        if (0 !== stripos($metadata->type, 'core-')) {
            // Application specific model.
            if (false === $this->manager->shouldAllowDbOperations()) {
                throw new \RuntimeException('No application has been defined. Database operations halted.');
            }
            if (true === $this->manager->hasApplication()) {
                $dbName = sprintf('%s-%s', $dbName, $this->manager->getDatabaseSuffix());
            }

        }
        $metadata->persistence->db = $dbName;
    }
}
