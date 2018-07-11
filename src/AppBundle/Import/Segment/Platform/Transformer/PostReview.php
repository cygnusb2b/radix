<?php

namespace AppBundle\Import\Segment\Platform\Transformer;

use AppBundle\Import\Segment\Transformer;

class PostReview extends Transformer
{

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->defineId('_id');
        $this->define('_id', '_id');

        $this->define('body', 'body');
        $this->define('createdDate', 'created');
        $this->define('anonymize', 'anonymize');
        $this->define('rating', 'rating');
        $this->define('ipAddress', 'ipAddress');  

        $this->define('displayName', 'displayName');
        $this->define('picture', 'picture');    

        $this->defineGlobal('deleted', 'deleted');
        $this->defineGlobal('approved', 'approved');

        $this->define('account', 'account');
        $this->defineGlobal('stream', 'stream');

        $this->define('legacy.id', '_id', 'strval');
        $this->defineStatic('legacy.source', 'platform_postreview');
    }

    public function deleted($data)
    {
        if ($data['status'] == 0) {
            return true;
        } else {
            return false;
        }
    }

    public function approved($data)
    {
        if ($data['status'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function stream($data)
    {
        if (!empty($data['stream'])) {
            $ref = [
                'id'   => (string) $data['stream'],
                'type' => 'post-stream'
            ];
            return $ref;
        } else {
            return null;
        }
    }

}
