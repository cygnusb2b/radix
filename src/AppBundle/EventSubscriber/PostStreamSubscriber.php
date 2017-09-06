<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Exception\HttpFriendlyException;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

/**
 * Handles post-stream models.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class PostStreamSubscriber implements EventSubscriberInterface
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
     * Processes post-stream models on any commit (create, update, or delete)
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        if (empty($model->get('identifier'))) {
            throw new HttpFriendlyException('The post stream identifier value cannot be empty.', 422);
        }
        if (empty($model->get('url'))) {
            throw new HttpFriendlyException('The post stream url value cannot be empty.', 422);
        }
    }

    /**
     * Determines if this subscriber should handle the model.
     * Must be a post-stream model.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'post-stream' === $model->getType();
    }
}
