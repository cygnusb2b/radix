<?php

namespace AppBundle\Integration;

use AppBundle\Integration\Execution;
use AppBundle\Integration\Task;
use AppBundle\Question\TypeManager;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use As3\Parameters\Parameters;
use As3\Bundle\PostProcessBundle\Task\TaskManager;

class IntegrationManager
{
    /**
     * @var null|Execution\AccountPushExecution[]
     */
    private $accountPushExecutions;

    /**
     * @var ServiceInterface[]
     */
    private $services = [];

    /**
     * @var Store
     */
    private $store;

    /**
     * @var TaskManager
     */
    private $taskManager;

    /**
     * @var TypeManager
     */
    private $typeManager;

    /**
     * @param   Store       $store
     * @param   TypeManager $typeManager
     */
    public function __construct(Store $store, TypeManager $typeManager, TaskManager $taskManager)
    {
        $this->store       = $store;
        $this->typeManager = $typeManager;
        $this->taskManager = $taskManager;
    }

    /**
     * Runs the account-push integration on an account create.
     *
     * @param   Model   $account    The account to push.
     */
    public function accountPushCreate(Model $account)
    {
        foreach ($this->loadAccountPushExecutions() as $execution) {
            $this->taskManager->addTask(new Task\AccountPushCreateTask($account, $execution));
            // $execution->runCreate($account);
        }
    }

    /**
     * Runs the account-push integration on an account delete.
     *
     * @param   Model   $account    The account to push.
     */
    public function accountPushDelete(Model $account)
    {
        foreach ($this->loadAccountPushExecutions() as $execution) {
            $execution->runDelete($account);
        }
    }

    /**
     * Runs the account-push integration on an account update.
     *
     * @param   Model   $account    The account to push.
     * @param   array   $changeSet  The model changeset.
     */
    public function accountPushUpdate(Model $account, array $changeSet)
    {
        foreach ($this->loadAccountPushExecutions() as $execution) {
            $execution->runUpdate($account, $changeSet);
        }
    }

    /**
     * Adds/registers a service instance.
     *
     * @param   ServiceInterface    $service
     * @return  self
     */
    public function addService(ServiceInterface $service)
    {
        $this->services[$service->getKey()] = $service;
        return $this;
    }

    /**
     * @return  Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Runs an indentify integration.
     *
     * @param   string  $pullKey        The pull key name to use.
     * @param   string  $externalId     The external, third-party identity identifier.
     * @return  Model|null The external-identity model pulled from the third-party service.
     */
    public function identify($pullKey, $externalId)
    {
        $integration = $this->retrieveIntegrationFor('identify', ['pullKey' => $pullKey]);
        if (false === $integration->get('enabled')) {
            return;
        }
        $service     = $this->loadServiceFor($integration);
        $handler     = $service->getIdentifyHandler();
        if (null === $handler) {
            $this->throwUnsupportedError('identify', $service->getKey());
        }
        $execution = new Execution\IdentifyExecution($integration, $service, $this);
        $execution->setHandler($handler);
        $execution->setTypeManager($this->typeManager);
        $model = $execution->run($externalId);

        $this->updateIntegrationDetails($integration);

        return $model;
    }

    /**
     * Runs question-pull integrations
     *
     * @param   string|null     $integrationId  The question-pull id to use. If none specified, will use all.
     */
    public function questionPull($integrationId = null)
    {
        $criteria = [];
        if (null !== $integrationId) {
            $criteria['id'] = $integrationId;
        }
        $integrations = $this->getStore()->findQuery('integration-question-pull', $criteria);
        foreach ($integrations as $integration) {
            if (false === $integration->get('enabled')) {
                continue;
            }

            $service = $this->loadServiceFor($integration);
            $handler = $service->getQuestionPullHandler();
            if (null === $handler) {
                $this->throwUnsupportedError('question-pull', $service->getKey());
            }
            $execution = new Execution\QuestionPullExecution($integration, $service, $this);
            $execution->setHandler($handler);
            $execution->run();

            $this->updateIntegrationDetails($integration);
        }
    }

    /**
     * Gets an integration service instance for the provided integration key.
     *
     * @param   string  $key
     * @return  ServiceInterface|null
     */
    private function getServiceFor($key)
    {
        if (isset($this->services[$key])) {
            return $this->services[$key];
        }
    }

    /**
     * Loads account-push execution objects.
     *
     * @return  Execution\AccountPushExecution[]
     */
    private function loadAccountPushExecutions()
    {
        if (null === $this->accountPushExecutions) {
            $executions   = [];
            $integrations = $this->getStore()->findQuery('integration-account-push', []);
            foreach ($integrations as $integration) {
                if (false === $integration->get('enabled')) {
                    continue;
                }
                $service = $this->loadServiceFor($integration);
                $handler = $service->getAccountPushHandler();
                if (null === $handler) {
                    $this->throwUnsupportedError('account-push', $service->getKey());
                }
                $execution = new Execution\AccountPushExecution($integration, $service, $this);
                $execution->setHandler($handler);

                $executions[$integration->getId()] = $execution;
            }
            $this->accountPushExecutions = $executions;
        }
        return $this->accountPushExecutions;
    }

    /**
     * Loads and initializes an integration service from the provided integration model.
     *
     * @param   Model   $integration
     * @return  ServiceInterface
     * @throws  \RuntimeException|\InvalidArgumentException
     */
    private function loadServiceFor(Model $integration)
    {
        if (null === $model = $integration->get('service')) {
            throw new \RuntimeException('No service was defined on the integration model.');
        }
        $key = str_replace('integration-service-', '', $model->getType());
        if (null === $service = $this->getServiceFor($key)) {
            throw new \InvalidArgumentException(sprintf('No integration service was found as a registered service for key `%s`', $key));
        }

        // Create the service parameters by extracting attributes from the model and configure the service instance.
        $parameters = [];
        foreach ($model->getMetadata()->getAttributes() as $key => $meta) {
            $parameters[$key] = $model->get($key);
        }
        $service->configure($parameters);
        if (false === $service->hasValidConfig()) {
            throw new \RuntimeException(sprintf('The integration service configuration for `%s` is not valid. Unable to proceed with execution.', $service->getKey()));
        }
        return $service;
    }

    /**
     * Retrieve an integration model from the database.
     *
     * @param   string  $type     The integration type.
     * @param   array   $criteria
     * @return  Model|null
     * @throws  \RuntimeException|\InvalidArgumentException
     */
    private function retrieveIntegrationFor($type, array $criteria)
    {
        $modelType = sprintf('integration-%s', $type);
        $integration = $this->store->findQuery($modelType, $criteria)->getSingleResult();

        if (null === $integration) {
            throw new \InvalidArgumentException(sprintf('No `%s` integration found for criteria: %s', $type, json_encode($criteria)));
        }
        return $integration;
    }

    private function throwUnsupportedError($integrationType, $serviceKey)
    {
        throw new \RuntimeException(sprintf('The `%s` integration type is not supported by the `%s` service.', $integrationType, $serviceKey));
    }

    /**
     * Updates the details of the integration.
     *
     * @param   Model   $integration
     */
    private function updateIntegrationDetails(Model $integration)
    {
        $now       = new \DateTime();
        $integration
            ->set('lastRunDate', $now)
            ->set('timesRan', (integer) $integration->get('timesRan') + 1)
        ;

        if (null === $integration->get('firstRunDate')) {
            $integration->set('firstRunDate', $now);
        }
        $integration->save();
    }
}
