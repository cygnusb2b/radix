<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Utility\LocaleUtility;
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
     * @todo    This should add smart logic: use zip to find state, use state to find country (if not set), etc.
     * @param   Model   $model
     */
    private function validateCodes(Model $model)
    {
        $regions    = LocaleUtility::getRegionsAll();
        $regionCode = $model->get('regionCode');

        if (!isset($regions[$regionCode])) {
            $model->get('regionCode', null);
            if (null === $model->get('region')) {
                $model->set('region', $regionCode);
            }
        } else {
            $model->set('region', $regions[$regionCode]);
        }

        $countries   = LocaleUtility::getCountries();
        $countryCode = $model->get('countryCode');

        if (!isset($countries[$countryCode])) {
            $model->get('countryCode', null);
            if (null === $model->get('country')) {
                $model->set('country', $countryCode);
            }
        } else {
            $model->set('country', $countries[$countryCode]);
        }
    }
}
