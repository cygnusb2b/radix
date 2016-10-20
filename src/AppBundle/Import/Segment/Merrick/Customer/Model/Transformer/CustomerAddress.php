<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model\Transformer;

use AppBundle\Import\Segment\Merrick\Customer\Model\Transformer;

class CustomerAddress extends Transformer
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        // Set up for reference passes
        $this->defineStatic('legacy.source', 'customer');
        $this->define('street', 'street');
        $this->define('extra', 'extra');
        $this->define('city', 'city');
        $this->define('postalCode', 'postalCode');
        $this->define('country', 'country');
        $this->define('countryCode', 'countryCode');
        $this->define('region', 'region');
        $this->define('regionCode', 'regionCode');
    }
}
