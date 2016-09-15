<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Utility\ModelUtility;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

class CustomerEmailSubscriber implements EventSubscriberInterface
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
        $this->formatEmailAddress($model);
        if (null === $model->get('customer')) {
            throw new \InvalidArgumentException('All customer email addresses must be assigned to a customer.');
        }
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'customer-email' === $model->getType();
    }

    /**
     * @param   Model   $model
     * @throws  \InvalidArgumentException
     */
    private function formatEmailAddress(Model $model)
    {
        $value = $model->get('value');
        $value = trim($value);
        if (empty($value)) {
            throw new \InvalidArgumentException('The customer email value cannot be empty.');
        }
        $value = strtolower($value);
        if (false === stripos($value, '@')) {
            throw new \InvalidArgumentException(sprintf('The provided email address "%s" is invalid.', $value));
        }
        $model->set('value', $value);
    }
}
