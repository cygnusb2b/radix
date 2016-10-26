<?php

namespace AppBundle\Integration;

use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

class IntegrationManager
{
    /**
     * @var ServiceInterface[]
     */
    private $services = [];

    /**
     * @var Store
     */
    private $store;

    /**
     * @param   Store    $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
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
     * Runs an indentification integration.
     *
     * @param   string  $pullKey        The pull key name to use.
     * @param   string  $externalId     The external, third-party identity identifier.
     */
    public function identify($pullKey, $externalId)
    {
        $integration = $this->retrieveIntegrationFor('identify', ['pullKey' => $pullKey]);
        $service     = $this->loadServiceFor($integration);
        $handler     = $service->getIdentifyHandler();
        if (null === $handler) {
            throw new \RuntimeException('Identify is not supported by the `%s` integration service.');
        }
        list($source, $identifier) = $handler->getSourceAndIdentifierFor($externalId);

        $identity = $this->store->findQuery('identity-external', ['source' => $source, 'identifier' => $identifier])->getSingleResult();
        if (null === $identity) {
            // Immediately create. Will update the model data later.
            $identity = $this->store->create('identity-external');
            $identity->set('source', $source);
            $identity->set('identifier', $identifier);
            $identity->save();
        }
        return $identity

        // @todo At this point, the actual identification and updating of the identity model should be handled post-process.
        $definition = $handler->execute($identifier);



        var_dump($source, $identifier, $definition);
        die();
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

        // Create the service parameters by extracted attributes from the model and configure the service instance.
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
        if (false === $integration->get('enabled')) {
            throw new \RuntimeException(sprintf('The integration is currently disabled for id `%s`', $integration->getId()));
        }
        return $integration;
    }
}
