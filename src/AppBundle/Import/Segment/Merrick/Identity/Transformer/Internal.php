<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Transformer;

class Internal extends Identity
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
