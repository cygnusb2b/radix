<?php

namespace AppBundle\Factory\Identity;

use AppBundle\Factory\AbstractEmbedFactory;
use AppBundle\Factory\Error;
use AppBundle\Utility\LocaleUtility;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Embed;

/**
 * Factory for identity addresses.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class IdentityAddressFactory extends AbstractEmbedFactory
{
    /**
     * {@inheritodc}
     */
    public function canSave(AbstractModel $address)
    {
        if (false === $this->supportsEmbed($address)) {
            return $this->getUnsupportedError();
        }
        $this->preValidate($address);
        return true;
    }

    /**
     * {@inheritodc}
     */
    public function preValidate(AbstractModel $address)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postSave(Model $model)
    {
    }

    /**
     * {@inheritodc}
     */
    public function postValidate(AbstractModel $address)
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

    /**
     * {@inheritodc}
     */
    protected function getSupportsType()
    {
        return 'identity-address';
    }
}
