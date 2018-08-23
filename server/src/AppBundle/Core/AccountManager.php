<?php

namespace AppBundle\Core;

use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\Request;

class AccountManager
{
    const PUBLIC_KEY_PARAM  = 'x-radix-appid';
    const USING_PARAM       = 'X-Radix-Using';
    const BUILD_PARAM       = 'X-Radix-Build';
    const ENV_KEY           = 'APP';
    const ENV_NEW_RELIC_APP = 'NEW_RELIC_APP_NAME';
    const APP_PATH          = '/app';

    /**
     * Origins that are considered global.
     * Is used for determining CORs access and management user access.
     *
     * @var array
     */
    private static $globalOrigins = [
        'http://docker.for.mac.host.internal:*',
        'http://dev.radix.as3.io:*',
        'http://radix.as3.io',
        'http://*.radix.as3.io',
        'https://radix.as3.io',
        'https://*.radix.as3.io',
    ];

    /**
     * @var Model|null
     */
    private $account;

    /**
     * @var string
     */
    private $buildKey;

    /**
     * @var bool
     */
    private $allowDbOps = true;

    /**
     * @var Model|null
     */
    private $application;

    public function __construct($buildKey)
    {
        $this->buildKey = $buildKey;
    }

    /**
     * Sets whether database operations are allowed.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function allowDbOperations($bit = true)
    {
        $this->allowDbOps = (boolean) $bit;
        return $this;
    }

    /**
     * Configures New Relic for the loaded acccount/application.
     *
     * @return  self
     */
    public function configureNewRelic()
    {
        $request = Request::createFromGlobals();
        if (extension_loaded('newrelic')) {
            newrelic_add_custom_parameter('application', $this->getCompositeKey());
            newrelic_add_custom_parameter('queryString', $request->getQueryString());
        }
        return $this;
    }

    /**
     * Determines the request context: one of application or core.
     *
     * @param   Request $request
     * @return  string
     */
    public function extractContextFrom(Request $request)
    {
        return 0 === stripos($request->getPathInfo(), self::APP_PATH) ? 'application' : 'core';
    }

    /**
     * Extracts the application public key from the Request.
     *
     * @param   Request $request
     * @return  string|null
     */
    public function extractPublicKeyFrom(Request $request)
    {
        $param   = self::PUBLIC_KEY_PARAM;
        $values  = [
            'header' => $request->headers->get($param),
            'query'  => $request->query->get($param),
        ];

        $publicKey = null;
        foreach ($values as $value) {
            if (!empty($value)) {
                $publicKey = $value;
                break;
            }
        }
        return empty($publicKey) ? null : $publicKey;
    }

    /**
     * @return  Model|null
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return  string
     */
    public function getBuildKey()
    {
        return $this->buildKey;
    }

    /**
     * @return  string|null
     */
    public function getCompositeKey()
    {
        if (false === $this->hasApplication()) {
            return;
        }
        return sprintf('%s:%s', $this->account->get('key'), $this->application->get('key'));
    }

    /**
     * @return  string|null
     */
    public function getDatabaseSuffix()
    {
        if (false === $this->hasApplication()) {
            return;
        }
        return sprintf('%s-%s', $this->account->get('key'), $this->application->get('key'));
    }

    /**
     * @return  string|null
     */
    public function getAccountKey()
    {
        return $this->account->get('key');
    }

    /**
     * @return  string|null
     */
    public function getApplicationKey()
    {
        return $this->application->get('key');
    }

    /**
     * Origins that are considered global.
     * Is used for determining CORs access and management user access.
     *
     * @see     \AppBundle\Cors\CorsListener::onKernelResponse()
     * @see     \AppBundle\Security\User\CoreUser::setOrigin()
     * @return  array
     */
    public static function getGlobalOrigins()
    {
        return self::$globalOrigins;
    }

    /**
     * Gets the app name used by New Relic.
     *
     * @return  string|false
     */
    public function getNewRelicAppName()
    {
        return getenv(self::ENV_NEW_RELIC_APP);
    }

    /**
     * @return  bool
     */
    public function hasApplication()
    {
        return null !== $this->application;
    }

    /**
     * @param   Model   $application
     * @return  self
     */
    public function setApplication(Model $application)
    {
        $this->appendApplicationSettings($application);
        $this->application = $application;
        $this->account     = $application->get('account');
        $this->configureNewRelic();
        return $this;
    }

    /**
     * Deteremines whether database operations are allowed.
     *
     * @return  bool
     */
    public function shouldAllowDbOperations()
    {
        if (true === $this->allowDbOps) {
            return true;
        }
        return $this->hasApplication();
    }

    /**
     * Ensures that the settings are pre-filled with defaults if empty.
     *
     * @param   Model   $application
     * @return  self
     */
    private function appendApplicationSettings(Model $application)
    {

        $settings = $application->get('settings');
        if (null === $settings) {
            $settings = $application->createEmbedFor('settings');
            $application->set('settings', $settings);
        }
        foreach ($settings->getMetadata()->getEmbeds() as $key => $embedMeta) {
            if (null === $settings->get($key)) {
                $settings->set($key, $settings->createEmbedFor($key));
            }
        }
    }
}
