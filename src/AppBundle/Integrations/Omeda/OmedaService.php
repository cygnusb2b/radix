<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\ServiceInterface;
use As3\OmedaSDK\ApiClient;

class OmedaService implements ServiceInterface
{
    /**
     * @var AccountPushHandler
     */
    private $accountPushHandler;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var IdentifyHandler
     */
    private $identifyHandler;

    /**
     * @var QuestionPullHandler
     */
    private $questionPullHandler;

    /**
     * Constructor.
     *
     * @param   ApiClient  $apiClient
     * @param   array|null $parameters
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient           = $apiClient;
        $this->accountPushHandler  = new AccountPushHandler();
        $this->identifyHandler     = new IdentifyHandler();
        $this->questionPullHandler = new QuestionPullHandler();
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
     * {@inheritdoc}
     */
    public function getAccountPushHandler()
    {
        $this->accountPushHandler->setService($this);
        return $this->accountPushHandler;
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
    public function getKey()
    {
        return 'omeda';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestionPullHandler()
    {
        $this->questionPullHandler->setService($this);
        return $this->questionPullHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function hasValidConfig()
    {
        return $this->apiClient->hasValidConfig();
    }
}
