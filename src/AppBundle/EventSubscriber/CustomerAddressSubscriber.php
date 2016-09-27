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

        $this->appendLocalityData($model);
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
     * @param   Model   $model
     */
    private function appendLocalityData(Model $model)
    {
        if (null !== $postalCode = $model->get('postalCode')) {
            $data = LocaleUtility::getLocalityDataFor($postalCode);
            if (is_array($data)) {
                list($city, $region, $country) = $data;
                if ('US' === $country || 'CA' === $country) {
                    if (null !== $city) {
                        $model->set('city', $city);
                    }
                    if (null !== $region) {
                        $model->set('regionCode', $region);
                    }
                    $countryCode = 'US' === $country ? 'USA' : 'CAN';
                    $model->set('countryCode', $countryCode);
                }
            }
        }
    }

    /**
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
