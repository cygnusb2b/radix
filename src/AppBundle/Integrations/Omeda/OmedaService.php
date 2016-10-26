<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\ServiceInterface;
use As3\OmedaSDK\ApiClient;

class OmedaService implements ServiceInterface
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var IdentifyHandler
     */
    private $identifyHandler;

    /**
     * Constructor.
     *
     * @param   ApiClient  $apiClient
     * @param   array|null $parameters
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient       = $apiClient;
        $this->identifyHandler = new IdentifyHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $parameters)
    {
        $this->apiClient->configure($parameters);
        $useStaging = isset($parameters['useStaging']) ? $parameters['useStaging'] : false;
        $this->apiClient->useStaging($useStaging);
        return $this;
    }

    /**
     * @return  ApiClient
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifyHandler()
    {
        $this->identifyHandler->setService($this);
        return $this->identifyHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function executeQuestionPull($externalId, array $extra = [])
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'omeda';
    }

    /**
     * {@inheritdoc}
     */
    public function hasValidConfig()
    {
        return $this->apiClient->hasValidConfig();
    }
}
