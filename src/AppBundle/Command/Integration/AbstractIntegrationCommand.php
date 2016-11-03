<?php

namespace AppBundle\Command\Integration;

use AppBundle\Integration\IntegrationManager;
use Symfony\Component\Console\Command\Command;

abstract class AbstractIntegrationCommand extends Command
{
    /**
     * @var IntegrationManager
     */
    private $manager;

    /**
     * Constructor.
     *
     * @param   IntegrationManager  $manager
     */
    public function __construct(IntegrationManager $manager)
    {
        $this->manager = $manager;
        parent::__construct(sprintf('app:integration:%s', $this->getType()));
    }

    /**
     * @return  IntegrationManager
     */
    final protected function getManager()
    {
        return $this->manager;
    }

    /**
     * Gets the integration type.
     *
     * @return  string
     */
    abstract protected function getType();
}
