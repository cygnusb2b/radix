<?php

namespace AppBundle\EventSubscriber\Customer;

use AppBundle\Factory\CustomerAddressFactory;
use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;
use As3\Modlr\Store\Store;

class CustomerAddressSubscriber extends AbstractCustomerSubscriber
{
    private $factory;

    public function __construct(CustomerAddressFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldProcess(Model $model)
    {
        return 'customer-address' === $model->getType();
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
