<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData\Transformer;

use AppBundle\Import\Segment\Transformer;
use AppBundle\Import\Segment\Merrick\Identity\Transformer\Address;

class Inquiry extends Transformer
{
    /**
     * {@inheritdoc}
     */
    protected $requiredFields = [
        'legacy.id',
        'legacy.source',
        'legacy.email'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        // $this->defineId('_id');
        $this->define('legacy.id', '_id', 'strval');
        $this->defineStatic('legacy.source', 'content_user_rel');
        $this->define('legacy.email', 'email', 'strtolower');

        $this->defineStatic('sourceKey', 'inquiry');
        $this->define('createdDate', 'timestamp', function($timestamp) {
            $date = new \DateTime();
            return $date->setTimestamp($timestamp);
        });

        $this->define('payload.identity.givenName', 'first_name');
        $this->define('payload.identity.familyName', 'last_name');
        // $this->define('payload.identity.primaryEmail', 'email');
        $this->define('payload.identity.primaryPhone', 'phone');
        $this->define('payload.identity.companyName', 'company_name');
        $this->defineGlobal('payload.identity.primaryAddress', 'primaryAddress');
        $this->define('legacy.identity._id', 'user_id', function($value) {
            if (is_string($value) && !empty($value)) {
                return new \MongoId((string) $value);
            }
        });

        $this->defineGlobal('legacy.answers.omeda', 'omedaAnswers');
        $this->define('legacy.answers.comments', 'comments');
        $this->define('legacy.answers.purchaseIntent', 'purchase_intent');
    }

    public function primaryAddress($data)
    {
        $transformer = new Address();
        $address = $transformer->toApp($data);
        if (count($address) > 2) {
            return $address;
        }
    }

    public function omedaAnswers($data)
    {
        $questions = [];
        foreach ($data as $key => $value) {
            if (false === stripos($key, 'omeda')) {
                continue;
            }
            $oKey = str_replace('omeda_', '', $key);
            // if (!is_numeric($oKey)) {
            //     continue;
            // }
            $questions[] = ['question' => $oKey, 'answer' => (string) $value];
        }
        return $questions;
    }
}
