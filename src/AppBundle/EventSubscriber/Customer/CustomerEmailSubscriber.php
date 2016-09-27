<?php

namespace AppBundle\EventSubscriber\Customer;

use AppBundle\Factory\CustomerEmailFactory;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;
use As3\Modlr\Store\Store;

class CustomerEmailSubscriber extends AbstractCustomerSubscriber
{
    private $factory;

    public function __construct(CustomerEmailFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldProcess(Model $model)
    {
        return 'customer-email' === $model->getType();
    }

    /**
     * {@inheritdoc}
     */
    protected function handleEventsFor(Model $model)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function getFactory()
    {
        return $this->factory;
    }
}
