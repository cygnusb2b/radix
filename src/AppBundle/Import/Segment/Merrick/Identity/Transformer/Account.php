<?php

namespace AppBundle\Import\Segment\Merrick\Identity\Transformer;

class Account extends Identity
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
