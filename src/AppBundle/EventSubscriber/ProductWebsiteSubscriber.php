<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

/**
 * Handles product-website models.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class ProductWebsiteSubscriber implements EventSubscriberInterface
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
     * Processes product-website models on any commit (create, update, or delete)
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        $this->formatUrl($model);
    }

    /**
     * Determines if this subscriber should handle the model.
     * Must be a product-website model.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'product-website' === $model->getType();
    }

    /**
     * @param   Model   $model
     */
    private function formatUrl(Model $model)
    {
        $url = $model->get('url');
        $model->set('url', ModelUtility::formatExternalUrlValue($url));
    }
}
