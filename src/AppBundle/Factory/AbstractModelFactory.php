<?php

namespace AppBundle\Factory;

use As3\Modlr\Store\Store;

/**
 * Abstract factory for AS3 models.
 * Must also implement the remaining methods of the subscriber interface.
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
     * @param   Store   $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * @return  Store
     */
    public function getStore()
    {
        return $this->store;
    }
}
