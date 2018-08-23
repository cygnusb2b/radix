<?php

namespace AppBundle\Import\Segment\Platform\Transformer;

use AppBundle\Import\Segment\Transformer;

class PostComment extends Transformer
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
        $this->define('ipAddress', 'ipAddress');
        $this->define('displayName', 'displayName');
        $this->define('picture', 'picture');

        $this->define('account', 'account');

        $this->defineGlobal('stream', 'stream');
        $this->defineGlobal('parent', 'parent');

        $this->defineGlobal('deleted', 'deleted');
        $this->defineGlobal('approved', 'approved');

        $this->define('legacy.id', '_id', 'strval');
        $this->defineStatic('legacy.source', 'platform_postcomment');
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

    public function parent($data)
    {
        if (!empty($data['parent'])) {
            $ref = [
                'id'   => (string) $data['parent'],
                'type' => 'post-comment'
            ];
            return $ref;
        } else {
            return null;
        }
    }

}
