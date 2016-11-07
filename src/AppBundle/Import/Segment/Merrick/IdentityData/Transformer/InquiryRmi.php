<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData\Transformer;

class InquiryRmi extends Inquiry
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        // $this->defineStatic('payload.meta.model.type', 'content');
        $this->define('payload.meta.model.identifier', 'content_id', 'intval');
    }
}
