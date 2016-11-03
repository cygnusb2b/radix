<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

use AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

class IdentityInternal extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->defineCallable('emails', 'email', 'identityEmail');
    }

    public function identityEmail($value)
    {
        return [
            [
                'identifier'    => (string) new \MongoId(),
                'isPrimary'     => true,
                'value'         => strtolower($value),
            ]
        ];
    }
}
