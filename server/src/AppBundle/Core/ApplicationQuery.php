<?php

namespace AppBundle\Core;

use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use Symfony\Component\HttpFoundation\Request;

class ApplicationQuery
{
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
     * @param   string  $appKey
     * @return  Model|null
     */
    public function retrieveByAppKey($appKey)
    {
        $parts = explode(':', $appKey);
        if (2 !== count($parts) || empty($parts[0]) || empty($parts[1])) {
            throw new \InvalidArgumentException('Invalid application key format.');
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
}
