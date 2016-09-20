<?php

namespace AppBundle\Core;

use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use Symfony\Component\HttpFoundation\Request;

class AccountManager
{
    const PUBLIC_KEY_PARAM = 'x-radix-appid';
    const USING_PARAM      = 'X-Radix-Using';
    const ENV_KEY          = 'APP';
    const APP_PATH         = '/app';

    /**
     * @var Model|null
     */
    private $account;

    /**
     * @var bool
     */
    private $allowDbOps = true;

    /**
     * @var Model|null
     */
    private $application;

    /**
     * @var Store
     */
    private $store;

    /**
     * @param   Store   $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
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
     * Gets the sesssion key for the provided request.
     *
     * @param   Request $request
     * @return  string
     */
    public function getSessionKeyFor(Request $request)
    {
        $context = $this->extractContextFrom($request);
        return sprintf('%s:%s', $context, self::PUBLIC_KEY_PARAM);
    }

    /**
     * @return  bool
     */
    public function hasApplication()
    {
        return null !== $this->application;
    }

    /**
     * @param   string  $appKey
     * @return  Model|null
     */
    public function retrieveByAppKey($appKey)
    {
        $parts = explode(':', $appKey);
        if (2 !== count($parts) || empty($parts[0]) || empty($parts[1])) {
            throw new \InvalidArgumentException('Invalid app key.');
        }
        $account = $this->store->findQuery('core-account', ['key' => $parts[0]])->getSingleResult();
        if (null === $account) {
            return;
        }
        $criteria = ['account' => $account->getId(), 'key' => $parts[1]];
        return $this->store->findQuery('core-application', $criteria)->getSingleResult();
    }

    /**
     * @param   string  $publicKey
     * @return  Model|null
     */
    public function retrieveByPublicKey($publicKey)
    {
        return $this->store->findQuery('core-application', ['publicKey' => $publicKey])->getSingleResult();
    }

    /**
     * @param   Model   $application
     * @return  self
     */
    public function setApplication(Model $application)
    {
        $this->application = $application;
        $this->account     = $application->get('account');
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
}