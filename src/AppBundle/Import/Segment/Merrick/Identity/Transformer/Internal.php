<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Transformer;

class Internal extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function __construct($serviceKey, $pushIntegrationId)
    {
        parent::__construct($serviceKey, $pushIntegrationId);

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
