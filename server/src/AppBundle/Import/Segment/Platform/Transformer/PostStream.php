<?php

namespace AppBundle\Import\Segment\Platform\Transformer;

use AppBundle\Import\Segment\Transformer;

class PostStream extends Transformer
{
    /**
     * {@inheritdoc}
     */
    public function __construct($serviceKey = null, $pushIntegrationId = null)
    {
        $this->defineId('_id');
        $this->define('_id', '_id');
        $this->defineStatic('test','testing');

        $this->define('title', 'title');
        $this->define('url', 'url');
        $this->define('identifier', 'streamId');

        $this->defineStatic('active', true);
        $this->defineStatic('deleted', false);

        $this->define('touchedDate', 'touched');

        $this->define('legacy.id', '_id', 'strval');
        $this->defineStatic('legacy.source', 'platform_poststream');
    }
}
