<?php

namespace AppBundle\EventSubscriber;

use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

/**
 * Handles models that utilize the timestampable mixin.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class TimestampableSubscriber implements EventSubscriberInterface
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
     * Processes timestampable models on any commit (create, update, or delete)
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }

        // @todo The updated date should only be changed if action was handled by a user...
        $now = microtime(true);
        if (true === $model->getState()->is('new')) {
            // New model. Set created, updated and touched date.
            $model->set('createdDate', $now);
            $model->set('touchedDate', $now);
            $model->set('updatedDate', $now);
        } elseif (true === $model->isDirty()) {
            // Dirty, existing model. Set touched date.
            $model->set('touchedDate', $now);
            $model->set('updatedDate', $now);
        }
    }

    /**
     * Determines if this subscriber should handle the model.
     * Must be a model that uses the timestampable mixin.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return $model->usesMixin('timestampable');
    }
}
