<?php

namespace AppBundle\Factory;

use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;

/**
 * Interface defining methods for factories that interface with event subscribers.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
interface SubscriberFactoryInterface extends ValidationFactoryInterface
{
    /**
     * Hook that is called after the save operation has been called in the subscriber.
     * Allows for sending model events that other services can attach to.
     * This method SHOULD not be called more than once, and should only be executed in an event subscriber.
     *
     * @param   Model   $model
     */
    public function postSave(Model $model);

    /**
     * Determines if this factory supports the provided model.
     *
     * @param   Model   $model
     * @return  bool
     */
    public function supports(Model $model);
}
