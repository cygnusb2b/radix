<?php

namespace AppBundle\Factory;

use As3\Modlr\Store\Store;

/**
 * Abstract factory for AS3 models.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
abstract class AbstractModelFactory
{
    /**
     * @var Store
     */
    private $store;

    /**
     * @return  Store
     * @throws  \RuntimeException
     */
    public function getStore()
    {
        if (null === $this->store) {
            throw new \RuntimeException('The Store service was not set to the factory. Unable to continue.');
        }
        return $this->store;
    }

    /**
     * @param   Store   $store
     * @return  self
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
        return $this;
    }
}
