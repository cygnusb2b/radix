<?php

namespace AppBundle\Core;

use As3\Modlr\Models\Model;

class AccountManager
{
    private $account;

    private $application;

    public function hasApplication()
    {
        return null !== $this->application;
    }

    public function setApplication(Model $application)
    {
        $this->application = $application;
        $this->account     = $application->get('account');
        return $this;
    }
}
