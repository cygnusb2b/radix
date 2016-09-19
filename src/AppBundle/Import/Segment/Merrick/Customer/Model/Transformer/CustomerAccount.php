<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model\Transformer;

use Aol\Transformers\Transformer;

class CustomerAccount extends Transformer
{
    // use \Aol\Transformers\Utilities\MongoDBTrait;

    public function __construct()
    {
        $this->define('_id', '_id');
        $this->define('givenName', 'first_name');
        $this->define('familyName', 'last_name');
        $this->define('gender', 'gender');
        $this->define('title', 'title');
        $this->defineDate('createdDate', 'created');
        $this->defineDate('updatedDate', 'updated');
        $this->defineDate('touchedDate', 'profile_updated');
        $this->define('deleted', '_id', function($value) {
            return false;
        });
    }

    public function defineDate($app, $ext)
    {
        return $this->define($app, $ext, function($value) {
            $date = null;
            if ($value instanceof \MongoDB\BSON\UTCDateTime) {
                $date = new \DateTime('@' . $value->toDateTime()->getTimestamp());
            } else if ($value instanceof \MongoDate) {
                $date = new \DateTime('@' . $value->sec);
            } else if (is_numeric($value)) {
                $date = new \DateTime('@' . $value);
            } else if (is_string($value)) {
                $date = new \DateTime(strtotime($value));
            }
            if ($date) {
                return new \MongoDate($date->format('U'));
            }
        });
    }
}
