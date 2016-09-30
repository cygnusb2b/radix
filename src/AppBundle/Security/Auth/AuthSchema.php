<?php

namespace AppBundle\Security\Auth;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Dictates the authentication schema/requirements.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class AuthSchema
{
    public function canUseSocial()
    {
        return !empty($this->getSocialProviders());
    }

    public function getSocialProviders()
    {
        return [];
    }

    public function minUsernameLength()
    {
        return 4;
    }

    public function requiresEmail()
    {
        return true;
    }

    public function requiresEmailWithSocial()
    {
        if (false === $this->requiresEmail()) {
            return false;
        }
        return false;
    }

    public function requiresUsername()
    {
        return false;
    }

    public function requiresUsernameWithSocial()
    {
        if (false === $this->requiresUsername()) {
            return false;
        }
        return false;
    }
}
