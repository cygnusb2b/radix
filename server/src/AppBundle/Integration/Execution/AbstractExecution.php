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
     * @throws  \InvalidArgumentException
     */
    public function __construct(Model $integration, ServiceInterface $service, IntegrationManager $manager)
    {
        if ($integration->getType() !== $this->getSupportedModelType()) {
            throw new \InvalidArgumentException(sprintf('The provided model integration type `%s` is not supported. Expected `%s`', $integration->getType(), $this->getSupportedModelType()));
        }
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
     * @return  array
     */
    final protected function extractExternalQuestionIds()
    {
        $integrations = $this->retrieveQuestionIntegrations();
        $identifiers  = [];
        foreach ($integrations as $integration) {
            $identifiers[$integration->get('identifier')] = true;
        }
        return array_keys($identifiers);
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
     * Gets the integration model type this execution supports.
     *
     * @return  string
     */
    abstract protected function getSupportedModelType();

    /**
     * Retrieves all question models that are currently pulling for this service.
     *
     * @return  Model[]
     */
    final protected function retrieveExternalQuestions()
    {
        $ids       = [];
        $questions = [];
        foreach ($this->retrieveQuestionIntegrations() as $integration) {
            $ids[] = $integration->getId();
        }
        if (empty($ids)) {
            return $questions;
        }
        $criteria   = ['pull' => ['$in' => $ids]];
        $collection = $this->getStore()->findQuery('question', $criteria);
        foreach ($collection as $question) {
            if (true === $question->get('deleted')) {
                continue;
            }
            $questions[$question->getId()] = $question;
        }
        return $questions;
    }

    /**
     * Updates the details of this integration.
     * Should be called after runtime.
     *
     * @param   Model   $integration
     */
    final protected function updateIntegrationDetails()
    {
        $now         = new \DateTime();
        $integration = $this->getIntegration();
        $integration
            ->set('lastRunDate', $now)
            ->set('timesRan', (integer) $integration->get('timesRan') + 1)
        ;

        if (null === $integration->get('firstRunDate')) {
            $integration->set('firstRunDate', $now);
        }
        $integration->save();
    }

    /**
     * Validates that this execution supports the appropriate handler implementation.
     *
     * @param   HandlerInterface    $handler
     * @throws  \InvalidArgumentException
     */
    abstract protected function validateImplements(HandlerInterface $handler);

    /**
     * @return  Model[]
     */
    private function retrieveQuestionIntegrations()
    {
        $criteria    = [
            'type'       => 'integration-question-pull',
            'service'    => $this->getIntegration()->get('service')->getId(),
            'boundTo'    => 'identity',
            'identifier' => ['$exists' => true]
        ];

        $integrations = [];
        $collection   = $this->getStore()->findQuery('integration', $criteria);
        foreach ($collection as $integration) {
            if (false === $integration->get('enabled')) {
                continue;
            }
            $integrations[] = $integration;
        }
        return $integrations;
    }
}
