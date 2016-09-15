<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

class CustomerAuthenticationSubscriber implements EventSubscriberInterface
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
        $this->validateRequiredFields($model);
        $this->appendDefaultRoles($model);
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'customer-authentication' === $model->getType();
    }

    /**
     * @param   Model   $model
     */
    private function appendDefaultRoles(Model $model)
    {
        $roles = $model->get('roles');
        if (!empty($roles)) {
            return;
        }
        foreach ($model->get('realm')->get('defaultRoles') as $role) {
            $roles[] = $role;
        }
        $model->set('roles', $roles);
    }

    /**
     * @param   Model   $model
     * @throws  \InvalidArgumentException
     */
    private function validateRequiredFields(Model $model)
    {
        $fields = ['realm', 'account', 'username', 'password'];
        foreach ($fields as $key) {
            $value = $model->get($key);
            if (null === $value) {
                throw new \InvalidArgumentException(sprintf('All customer auth objects must have the "%s" field set.', $key));
            }
        }
    }
}
