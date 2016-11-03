<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

use AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

class IdentityExternal extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::_construct();

        $this->defineGlobal('emails', 'identityEmail');
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
