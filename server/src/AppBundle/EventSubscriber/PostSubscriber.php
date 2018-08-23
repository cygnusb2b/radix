<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Exception\HttpFriendlyException;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

/**
 * Handles post models.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class PostSubscriber implements EventSubscriberInterface
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
     * Processes post models on any commit (create, update, or delete)
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }

        if (null === $model->get('stream')) {
            throw new HttpFriendlyException('All posts require a stream.', 422);
        }
        if ($model->getState()->is('new') && !$model->get('stream')->get('active')) {
            throw new HttpFriendlyException('New posts can no longer be added to this stream, as it is no longer active.', 422);
        }

        foreach (['body', 'displayName'] as $key) {
            $value = $model->get($key);
            $value = trim(strip_tags($value));
            if (empty($value)) {
                throw new HttpFriendlyException(sprintf('The post `%s` value cannot be empty.', $key), 422);
            }
            $model->set($key, $value);
        }
    }

    /**
     * Determines if this subscriber should handle the model.
     * Must be a post model.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'post-comment' === $model->getType() || 'post-review' === $model->getType();
    }
}
