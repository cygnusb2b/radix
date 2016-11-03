<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

use AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

class IdentityAddress extends Transformer
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->defineStatic('legacy.source', 'customer');
        $this->define('street', 'street');
        $this->define('extra', 'extra');
        $this->define('city', 'city');
        $this->define('postalCode', 'postalCode');
        $this->defineCallable('country', 'country', 'country');
        $this->defineCallable('countryCode', 'countryCode', 'countryCode');
        $this->defineCallable('region', 'region', 'region');
        $this->defineCallable('regionCode', 'regionCode', 'regionCode');
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
