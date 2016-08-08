<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

/**
 * Handles models that utilize the keyable mixin.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class KeyableSubscriber implements EventSubscriberInterface
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
     * Processes keyable models on any commit (create, update, or delete)
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        $this->appendNameAndKey($model);
    }

    /**
     * Appends (formats) the name and key to the model.
     * Will only set the key based off the name if the current key is empty.
     *
     * @param   Model   $model
     */
    protected function appendNameAndKey(Model $model)
    {
        $name = $model->get('name');
        $name = trim($name);

        $model->set('name', $name);
        $key = (null === $model->get('key')) ? $name : $model->get('key');
        $model->set('key', ModelUtility::sluggifyValue($key));

        if (empty($name) || empty($key)) {
            throw new \InvalidArgumentException(sprintf('All "%s" models must have the name and key fields defined.', $model->getType()));
        }
    }

    /**
     * Determines if this subscriber should handle the model.
     * Must be a model that uses the keyable mixin.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return $model->usesMixin('keyable');
    }
}
