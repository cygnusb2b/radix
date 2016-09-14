<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

class AuthenticationRealmWebsiteSubscriber implements EventSubscriberInterface
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
        $this->validateProduct($model);
        $this->appendKey($model);
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'authentication-realm-website' === $model->getType();
    }

    /**
     * @param   Model   $model
     * @throws  \InvalidArgumentException
     */
    private function appendKey(Model $model)
    {
        $key = $model->get('website')->get('key');
        $model->set('key', $key);
        if (empty($key)) {
            throw new \InvalidArgumentException('All website auth realms must have a key.');
        }
        $name = $model->get('website')->get('name');
        $model->set('name', $name);

    }

    /**
     * @param   Model   $model
     * @throws  \InvalidArgumentException
     */
    private function validateProduct(Model $model)
    {
        $website = $model->get('website');
        if (null === $website) {
            throw new \InvalidArgumentException('All website auth realms must be related to a website product');
        }
    }
}
