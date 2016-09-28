<?php

namespace AppBundle\Factory;

use AppBundle\Utility\LocaleUtility;
use As3\Modlr\Models\Model;

/**
 * Factory for customer addresses.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerAddressFactory extends AbstractModelFactory
{
    public function apply(Model $address, array $attributes = [])
    {
        $metadata = $address->getMetadata();
        foreach ($attributes as $key => $value) {
            if (true === $metadata->hasAttribute($key)) {
                $address->set($key, $value);
            }
        }
        return $address;
    }

    /**
     * Creates a new customer address for a customer and applies root attributes
     *
     * @param   Model   $customer
     * @param   array   $attributes
     * @return  Model
     */
    public function create(Model $customer, array $attributes = [])
    {
        $address = $this->getStore()->create('customer-address');
        $this->apply($address, $attributes);

        $address->set('customer', $customer);
        return $address;
    }

    /**
     * Determines if the customer address model can be saved.
     *
     * @param   Model   $address
     * @return  true|Error
     */
    public function canSave(Model $address)
    {
        $this->preValidate($address);
        if (null === $address->get('customer')) {
            // Ensure a customer has been assigned.
            return new Error('All customer addresses must be assigned to a customer.');
        }
        return true;
    }

    /**
     * Actions that always run (during save) before validation occurs.
     *
     * @param   Model   $address
     */
    public function preValidate(Model $address)
    {
    }

    /**
     * Actions that always run (during save) after validation occurs.
     *
     * @param   Model   $address
     */
    public function postValidate(Model $address)
    {
        $changeset = $address->getChangeSet();

        if (isset($changeset['attributes']['postalCode']) && null !== $postalCode = $address->get('postalCode')) {
            // Append additional locality data from postal code (city/state/country)
            $data = LocaleUtility::getLocalityDataFor($postalCode);
            if (is_array($data)) {
                list($city, $region, $country) = $data;
                if ('US' === $country || 'CA' === $country) {
                    if (null !== $city) {
                        $address->set('city', $city);
                    }
                    if (null !== $region) {
                        $address->set('regionCode', $region);
                    }
                    $countryCode = 'US' === $country ? 'USA' : 'CAN';
                    $address->set('countryCode', $countryCode);
                }
            }
        }

        // Ensure region/region code set correctly
        $regions    = LocaleUtility::getRegionsAll();
        $regionCode = $address->get('regionCode');

        if (!isset($regions[$regionCode])) {
            $address->get('regionCode', null);
            if (null === $address->get('region')) {
                $address->set('region', $regionCode);
            }
        } else {
            $address->set('region', $regions[$regionCode]);
        }

        // Ensure country/country code set correctly
        $countries   = LocaleUtility::getCountries();
        $countryCode = $address->get('countryCode');

        if (!isset($countries[$countryCode])) {
            $address->get('countryCode', null);
            if (null === $address->get('country')) {
                $address->set('country', $countryCode);
            }
        } else {
            $address->set('country', $countries[$countryCode]);
        }
    }
}
