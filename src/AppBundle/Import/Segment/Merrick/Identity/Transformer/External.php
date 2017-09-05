<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Transformer;

class External extends Internal
{
    /**
     * {@inheritdoc}
     */
    public function __construct($serviceKey, $brandKey)
    {
        parent::__construct($serviceKey, $brandKey);

        $this->define('identifier', 'omeda_id');
        $this->defineStatic('source', sprintf('identify:%s:%s', $serviceKey, $brandKey));
    }
}
