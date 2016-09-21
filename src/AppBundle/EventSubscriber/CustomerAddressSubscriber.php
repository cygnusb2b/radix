<?php

namespace AppBundle\EventSubscriber;

use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;

class CustomerAddressSubscriber implements EventSubscriberInterface
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
        $this->validateCodes($model);
    }

    /**
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'customer-address' === $model->getType();
    }

    /**
     * @todo    Eventually this should validate the code values against ISO
     * @param   Model   $model
     */
    private function validateCodes(Model $model)
    {
        $regionCode = $model->get('regionCode');
        if (null !== $regionCode) {
            $regionCode = (2 !== strlen($regionCode)) ? null : strtoupper($regionCode);
            $model->set('regionCode', $regionCode);
        }
        $countryCode = $model->get('countryCode');
        if (null !== $countryCode) {
            $countryCode = (3 !== strlen($countryCode)) ? null : strtoupper($countryCode);
            $model->set('countryCode', $countryCode);
        }
    }
}
