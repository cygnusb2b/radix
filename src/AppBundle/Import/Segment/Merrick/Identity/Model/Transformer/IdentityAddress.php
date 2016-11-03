<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

use AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

class IdentityAddress extends Transformer
{
    protected $requiredFields = ['identifier'];
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->defineStatic('identifier', (string) new \MongoId());
        $this->defineStatic('isPrimary', true);
        $this->define('street', 'address1');
        $this->define('extra', 'address2');
        $this->define('city', 'city');
        $this->define('postalCode', 'postal_code');
        $this->defineCallable('country', 'country', 'country');
        $this->defineCallable('countryCode', 'country', 'countryCode');
        $this->defineCallable('region', 'region', 'region');
        $this->defineCallable('regionCode', 'region', 'regionCode');
    }

    public function country($value)
    {
        if (strlen($value) > 3) {
            return $value;
        }
    }

    public function countryCode($value)
    {
        if (strlen($value) === 3) {
            return $value;
        }
    }

    public function region($value)
    {
        if (strlen($value) > 3) {
            return $value;
        }
    }

    public function regionCode($value)
    {
        if (strlen($value) <= 3) {
            return $value;
        }
    }
}
