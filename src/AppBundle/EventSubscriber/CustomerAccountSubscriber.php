<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

class CustomerAccountSubscriber implements EventSubscriberInterface
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
        $this->appendSettings($model);
        $this->validateCredentials($model);
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'customer-account' === $model->getType();
    }

    /**
     * @param   Model   $model
     */
    private function appendSettings(Model $model)
    {
        $settings = $model->get('settings');
        if (null === $settings) {
            $settings = $model->createEmbedFor('settings');
            $settings->set('enabled', true);
            $settings->set('locked', false);
            $settings->set('shadowbanned', false);
            $model->set('settings', $settings);
        }
    }

    /**
     * @param   Model   $model
     */
    private function validateCredentials(Model $model)
    {
        $credentials = $model->get('credentials');
        if (null === $credentials) {
            throw new HttpFriendlyException('Customer accounts must define at least one set of credentials.', 400);
        }
        if (null === $credentials->get('password') && 0 === count($credentials->get('social'))) {
            throw new HttpFriendlyException('Customer accounts must define at least one set of credentials (password or social)', 400);
        }

        $password = $credentials->get('password');
        if (null !== $password && empty($password->get('value'))) {
            throw new HttpFriendlyException('Customer passwords cannot be empty.', 400);
        }

        $fields = ['provider', 'authProvider', 'identifier'];
        foreach ($credentials->get('social') as $social) {
            foreach ($fields as $field) {
                if (empty($social->get($field))) {
                    throw new HttpFriendlyException(sprintf('Customer social credentials require the "%s" field.', $field), 400);
                }
            }
        }
    }
}
