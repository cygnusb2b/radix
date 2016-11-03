<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

use AppBundle\Import\Segment\Merrick\Identity\Model\Transformer;

class IdentityAccount extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->define('legacy.email', 'email', 'strtolower');
        $this->defineGlobal('credentials', 'credentials');

    }

    public function credentials($data)
    {
        $credentials = [];
        if (isset($data['pwd'])) {
            $credentials['password'] = [
                'value'     => $data['pwd'],
                'salt'      => isset($data['salt']) ? $data['salt'] : null,
                'mechanism' => 'merrick'
            ];
        }
        return $credentials;
    }
}
