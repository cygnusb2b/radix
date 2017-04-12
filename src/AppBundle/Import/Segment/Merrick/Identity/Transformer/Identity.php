<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Transformer;

use AppBundle\Import\Segment\Transformer;

class Identity extends Transformer
{
    protected $serviceKey;
    protected $pushIntegrationId;

    /**
     * {@inheritdoc}
     */
    public function __construct($serviceKey = null, $pushIntegrationId = null)
    {
        $this->serviceKey = $serviceKey;
        $this->pushIntegrationId = $pushIntegrationId;

        $this->defineId('_id');
        $this->define('legacy.id', '_id', 'strval');
        $this->defineStatic('legacy.source', 'users_v2');
        $this->defineGlobal('legacy.industry', 'industry');

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

        $this->define('legacy.omeda_id', 'omeda_id', 'strval');
        $this->define('legacy.omeda_encrypted_id', 'omeda_encrypted_id', 'strval');

        // Global passes for multi fields
        $this->defineGlobal('addresses', 'addresses');
        $this->defineGlobal('phones', 'phones');
        $this->defineGlobal('legacy.questions', 'questions');
        $this->defineGlobal('integration.push', 'integrationPush');
    }

    public function industry($data)
    {
        foreach ($data as $k => $v) {
            if (false !== stristr($k, '_industry')) {
                return $v;
            }
        }
    }

    public function addresses($data)
    {
        $transformer = new Address();
        $address = $transformer->toApp($data);
        if (count($address) > 2) {
            return [$address];
        }
    }

    public function integrationPush($data)
    {
        if (null === $this->pushIntegrationId || null == $this->serviceKey) {
            return;
        }
        if (!isset($data['omeda_id'])) {
            return;
        }
        return [
            [
                'identifier'    => $data['omeda_id'],
                'integrationId' => $this->pushIntegrationId,
                'timesRan'      => 0,
            ]
        ];
    }

    public function phones($data)
    {
        $phones = [];
        if (isset($data['phone'])) {
            $value = trim($data['phone']);
            if (!empty($value)) {
                $phones[] = [
                    'identifier' => (string) new \MongoId(),
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
                    'identifier' => (string) new \MongoId(),
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
