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

        $this->defineStatic('settings', ['enabled' => true, 'locked' => false, 'shadowbanned' => false]);
        $this->defineStatic('roles', ['USER']);
        $this->defineStatic('deleted', false);

        $this->define('history.lastLogin', 'last_login');
        $this->define('createdDate', 'created');
        $this->define('updatedDate', 'profile_updated');
        $this->define('touchedDate', 'updated');
        $this->define('givenName', 'first_name');
        $this->define('familyName', 'last_name');
        $this->define('title', 'title');
        $this->define('companyName', 'company_name');
        $this->define('displayName', 'display_name');
        $this->define('picture', 'photo_url');
        $this->define('gender', 'gender', function($val) {
            switch (strtolower($val)) {
                case 'm':
                    return 'Male';
                case 'f':
                    return 'Female';
            }
        });

        // Set up for reference passes
        $this->define('legacy.address.street', 'address1');
        $this->define('legacy.address.extra', 'address2');
        $this->define('legacy.address.city', 'city');
        $this->define('legacy.address.postalCode', 'postal_code');
        $this->defineCallable('legacy.address.country', 'country', 'country');
        $this->defineCallable('legacy.address.countryCode', 'country', 'countryCode');
        $this->defineCallable('legacy.address.region', 'region', 'region');
        $this->defineCallable('legacy.address.regionCode', 'region', 'regionCode');

        // Global passes for multi fields
        $this->defineGlobal('credentials', 'credentials');
        $this->defineGlobal('phones', 'phones');
        $this->defineGlobal('externalIds', 'externalIds');
        $this->defineGlobal('legacy.questions', 'questions');
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

    public function credentials($data)
    {
        $credentials = [];
        if (isset($data['pwd'])) {
            $credentials['password'] = [
                'value'     => $data['pwd'],
                'salt'      => isset($data['salt']) ? $data['salt'] : null,
                'mechanism' => 'merick'
            ];
        }
        return $credentials;
    }

    public function phones($data)
    {
        $phones = [];
        if (isset($data['phone'])) {
            $value = trim($data['phone']);
            if (!empty($value)) {
                $phones[] = [
                    'isPrimary' => 0 === count($phones),
                    'number'    => $value,
                    'phoneType' => 'Phone'
                ];
            }
        }

        if (isset($data['mobile'])) {
            $value = trim($data['mobile']);
            if (!empty($value)) {
                $phones[] = [
                    'isPrimary' => 0 === count($phones),
                    'number'    => $value,
                    'phoneType' => 'Mobile'
                ];
            }
        }
        return $phones;
    }

    public function questions($data)
    {
        $questions = [];
        foreach ($data as $key => $value) {
            if (false === stripos($key, 'omeda')) {
                continue;
            }
            $oKey = str_replace('omeda_', '', $key);
            if (!is_numeric($oKey)) {
                continue;
            }
            $questions[] = ['question' => $oKey, 'answer' => (string) $value];
        }
        return $questions;
    }
}
