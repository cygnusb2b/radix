<?php

namespace AppBundle\EventSubscriber\Customer;

use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Store;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

abstract class AbstractCustomerSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            Events::preCommit,
        ];
    }

    /**
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }

        $factory = $this->getFactoryWith($model->getStore());

        if (true !== $result = $factory->canSave($model)) {
            $result->throwException();
        }
        $factory->postValidate($model);

        $this->handleEventsFor($model);
    }

    /**
     * Gets the factory based on specific DI requirements.
     *
     * @return  object
     */
    abstract protected function getFactory();

    /**
     * Handles emitting create/update events for the model, if applicable.
     *
     * @param   Model   $model
     */
    abstract protected function handleEventsFor(Model $model);

    /**
     * Gets the factory instance with the Store injected.
     *
     * @param   Store   $store
     * @return  object
     */
    protected function getFactoryWith(Store $store)
    {
        $factory = $this->getFactory();
        $factory->setStore($store);
        return $factory;
    }

    /**
     * Determines if the model should processed by this subscriber.
     *
     * @param   Model   $model
     * @return  bool
     */
    abstract protected function shouldProcess(Model $model);
}
