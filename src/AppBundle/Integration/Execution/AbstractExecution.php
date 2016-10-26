<?php

namespace AppBundle\Integration\Execution;

use AppBundle\Integration\Handler\HandlerInterface;
use AppBundle\Integration\IntegrationManager;
use AppBundle\Integration\ServiceInterface;
use As3\Modlr\Models\Model;

abstract class AbstractExecution
{
    /**
     * @var Model
     */
    private $integration;

    /**
     * @var IntegrationManager
     */
    private $manager;

    /**
     * @var ServiceInterface
     */
    private $service;

    /**
     * @param   Model               $integration
     * @param   ServiceInterface    $service
     * @param   IntegrationManager  $manager
     */
    public function __construct(Model $integration, ServiceInterface $service, IntegrationManager $manager)
    {
        $this->integration  = $integration;
        $this->service      = $service;
        $this->manager      = $manager;
    }

    /**
     * Sets the third-party handler for this execution.
     *
     * @final
     * @param   HandlerInterface    $handler
     * @return  self
     * @throws  \InvalidArgumentException
     */
    final public function setHandler(HandlerInterface $handler)
    {
        $this->validateImplements($handler);
        $this->handler = $handler;
        return $this;
    }

    /**
     * Gets the third-party handler for this execution.
     *
     * @final
     * @return  HandlerInterface
     * @throws  \RuntimeException
     */
    final protected function getHandler()
    {
        if (null === $this->handler) {
            throw new \RuntimeException('No handler was set for this execution.');
        }
        return $this->handler;
    }

    /**
     * Gets the integration model.
     *
     * @final
     * @return  Model
     */
    final protected function getIntegration()
    {
        return $this->integration;
    }

    /**
     * Gets the integration manager.
     *
     * @final
     * @return  IntegrationManager
     */
    final protected function getManager()
    {
        return $this->manager;
    }

    /**
     * Gets the integration service.
     *
     * @final
     * @return  ServiceInterface
     */
    final protected function getService()
    {
        return $this->service;
    }

    /**
     * Gets the model store.
     *
     * @final
     * @return  \As3\Modlr\Store\Store
     */
    final protected function getStore()
    {
        return $this->manager->getStore();
    }

    /**
     * Validates that this execution supports the appropriate handler implementation.
     *
     * @param   HandlerInterface    $handler
     * @throws  \InvalidArgumentException
     */
    abstract protected function validateImplements(HandlerInterface $handler);
}
