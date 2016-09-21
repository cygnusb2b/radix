<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model\Transformer;

use AppBundle\Import\Segment\Merrick\Customer\Model\Transformer;

class Customer extends Transformer
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->defineId('_id');
        $this->define('legacy.id', '_id', 'strval');
        $this->defineStatic('legacy.source', 'users_v2');

        $this->define('legacy.auth.password', 'pwd');
        $this->define('legacy.auth.salt', 'salt');

        $this->define('legacy.origin', 'origin');
        $this->define('legacy.type', 'type');

        $this->defineStatic('deleted', false);
        $this->define('createdDate', 'created');
        $this->define('updatedDate', 'profile_updated');
        $this->define('touchedDate', 'updated');

        $this->define('givenName', 'first_name');
        $this->define('familyName', 'last_name');
        $this->define('gender', 'gender');
        $this->define('title', 'title');
        $this->define('companyName', 'company_name');

        $this->define('legacy.address.street', 'address1');
        $this->define('legacy.address.extra', 'address2');
        $this->define('legacy.address.city', 'city');
        $this->define('legacy.address.postalCode', 'postal_code');
        $this->defineCallable('legacy.address.country', 'country', 'country');
        $this->defineCallable('legacy.address.countryCode', 'country', 'countryCode');
        $this->defineCallable('legacy.address.region', 'region', 'region');
        $this->defineCallable('legacy.address.regionCode', 'region', 'regionCode');

        $this->define('legacy.email', 'email');
        $this->define('legacy.phones.phone', 'phone');
        $this->define('legacy.phones.mobile', 'mobile');

        $this->defineGlobal('externalIds', 'externalIds');
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

    public function externalIds($data)
    {
        $externalIds = [];

        if (isset($data['omeda_id']) && !empty($data['omeda_id'])) {
            $externalIds[] = [
                'identifier'    => (string) $data['omeda_id'],
                'source'        => 'omeda',
            ];
        }

        if (isset($data['omeda_encrypted_id']) && !empty($data['omeda_encrypted_id'])) {
            $externalIds[] = [
                'identifier'    => (string) $data['omeda_encrypted_id'],
                'source'        => 'omeda',
                'extra'         => ['encrypted'  => true]
            ];
        }
        return $externalIds;
    }
}
