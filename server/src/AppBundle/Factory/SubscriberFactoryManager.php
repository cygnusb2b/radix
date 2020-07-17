<?php

namespace AppBundle\Factory;

use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Contains all registered subscriber factories.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class SubscriberFactoryManager
{
    /**
     * @var SubscriberFactoryInterface[]
     */
    private $factories = [];

    /**
     * @param   SubscriberFactoryInterface  $factory
     */
    public function addFactory(SubscriberFactoryInterface $factory)
    {
        $this->factories[get_class($factory)] = $factory;
        return $this;
    }

    /**
     * @param   Model   $model
     * @return  SubscriberFactoryInterface|null
     */
    public function getFactoryFor(Model $model)
    {
        foreach ($this->factories as $factory) {
            if (true === $factory->supports($model)) {
                return $factory;
            }
        }
    }
}
