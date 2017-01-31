<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData\Transformer;

use AppBundle\Import\Segment\Transformer;
use AppBundle\Import\Segment\Merrick\Identity\Transformer\Address;

class GatedDownload extends Transformer
{
    /**
     * {@inheritdoc}
     */
    protected $requiredFields = [
        'legacy.id',
        'legacy.source',
        //'legacy.email'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->define('payload.meta.contentId', 'content_id');
        $this->define('payload.meta.type', 'type');
        $this->define('payload.meta.title', 'title');

        $this->defineStatic('sourceKey', 'gated-download');
        $this->define('createdDate', 'timestamp', function($timestamp) {
            $date = new \DateTime();
            return $date->setTimestamp($timestamp);
        });

        $this->define('legacy.id', '_id', 'strval');
        $this->defineStatic('legacy.source', 'content_user_rel');
        $this->define('legacy.email', 'email', 'strtolower');

        $this->define('payload.identity.givenName', 'first_name');
        $this->define('payload.identity.familyName', 'last_name');
        $this->define('payload.identity.primaryPhone', 'phone');
        $this->define('payload.identity.companyName', 'company_name');
        $this->defineGlobal('payload.identity.primaryAddress', 'primaryAddress');
        $this->define('legacy.identity._id', 'user_id', function($value) {
            if (is_string($value) && !empty($value)) {
                return new \MongoId((string) $value);
            }
        });

    }

    public function primaryAddress($data)
    {
        $transformer = new Address();
        $address = $transformer->toApp($data);
        if (count($address) > 2) {
            return $address;
        }
    }

}
