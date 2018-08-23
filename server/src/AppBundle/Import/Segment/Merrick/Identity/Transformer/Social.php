<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Transformer;

class Social extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->define('legacy.id', 'user_id');
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
