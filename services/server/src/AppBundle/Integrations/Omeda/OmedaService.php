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
     * @var array
     */
    private $behaviorIds = [];

    /**
     * @var string
     */
    private $env;

    /**
     * @var IdentifyHandler
     */
    private $identifyHandler;

    /**
     * @var OptInPushHandler
     */
    private $optInPushHandler;

    /**
     * @var QuestionPullHandler
     */
    private $questionPullHandler;

    /**
     * Constructor.
     *
     * @param   ApiClient   $apiClient
     * @param   string      $env
     */
    public function __construct(ApiClient $apiClient, $env)
    {
        $this->apiClient           = $apiClient;
        $this->env                 = $env;
        $this->accountPushHandler  = new AccountPushHandler();
        $this->identifyHandler     = new IdentifyHandler();
        $this->optInPushHandler    = new OptInPushHandler();
        $this->questionPullHandler = new QuestionPullHandler();
    }

    /**
     * @param   string  $type
     * @param   int     $identifier
     * @return  self
     */
    public function addBehaviorIdFor($type, $identifier)
    {
        $this->behaviorIds[strtolower($type)] = (integer) $identifier;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $parameters)
    {
        $this->apiClient->configure($parameters);
        $useStaging = isset($parameters['useStaging']) ? $parameters['useStaging'] : false;
        if ('prod' !== $this->env) {
            $useStaging = true;
        }
        $this->apiClient->useStaging($useStaging);

        foreach (['account' => 'accountBehaviorId', 'identity' => 'identityBehaviorId'] as $type => $key) {
            if (isset($parameters[$key])) {
                $this->addBehaviorIdFor($type, $parameters[$key]);
            }
        }
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
     * @param   string  $type
     * @return  integer|null
     */
    public function getBehaviorIdFor($type)
    {
        if (isset($this->behaviorIds[$type])) {
            return (integer) $this->behaviorIds[$type];
        }
    }

    /**
     * @return  array
     */
    public function getBehaviorIds()
    {
        return $this->behaviorIds;
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
    public function getOptInPushHandler()
    {
        $this->optInPushHandler->setService($this);
        return $this->optInPushHandler;
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
