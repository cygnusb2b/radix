<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

use AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

class IdentityExternal extends IdentityInternal
{
    /**
     * {@inheritdoc}
     */
    public function __construct($serviceKey, $brandKey)
    {
        parent::_construct();

        $this->define('identifier', 'omeda_id');
        $this->defineStatic('source', sprintf('identify:%s:%s', $serviceKey, $brandKey));
    }
}
