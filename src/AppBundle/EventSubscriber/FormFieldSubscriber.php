<?php

namespace AppBundle\EventSubscriber;

use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

class FormFieldSubscriber implements EventSubscriberInterface
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
        switch ($model->getType()) {
            case 'form-field-identity':
                $this->handleIdentityField($model);
                break;
            case 'form-field-question':
                $this->handleQuestionField($model);
                break;
        }
        if (null === $model->get('form')) {
            throw new \InvalidArgumentException('All field objects must be related to a form definition.');
        }
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'form-field' === $model->getMetadata()->getParentEntityType();
    }

    /**
     * @param   Model   $model
     * @throws  \InvalidArgumentException
     */
    private function handleIdentityField(Model $model)
    {
        $key = trim($model->get('key'));
        if (empty($key)) {
            throw new \InvalidArgumentException('All identity field objects must contain a value for the `key` field.');
        }
        $model->set('key', $key);

        $label = $model->get('label');
        if (empty($label)) {
            $model->set('label', $key);
        }
    }

    /**
     * @param   Model   $model
     * @throws  \InvalidArgumentException
     */
    private function handleQuestionField(Model $model)
    {
        if (null === $model->get('question')) {
            throw new \InvalidArgumentException('All question field objects must be related to a question.');
        }
    }
}
