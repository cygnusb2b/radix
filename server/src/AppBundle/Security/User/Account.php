<?php

namespace AppBundle\Security\User;

use \Serializable;
use As3\Modlr\Models\Model;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class Account implements AdvancedUserInterface, Serializable
{
    private $account;
    private $enabled = false;
    private $familyName;
    private $givenName;
    private $locked = true;
    private $mechanism;
    private $password;
    private $roles = [];
    private $salt;
    private $username;

    public function __construct(Model $account)
    {
        $this->account = $account;

        $this->familyName  = $account->get('familyName');
        $this->givenName   = $account->get('givenName');

        // @todo Will need to account for how social users get loaded here.
        // Or will (likely) need a new user class.

        $credentials = $account->get('credentials');

        if (null !== $credentials && null !== $password = $credentials->get('password')) {
            $this->password   = $password->get('value');
            $this->salt       = $password->get('salt');
            $this->mechanism  = $password->get('mechanism');
            $this->username   = $account->getId();

        }
        if (null !== $settings = $account->get('settings')) {
            $this->locked  = $settings->get('locked');
            $this->enabled = $settings->get('enabled');
        }
        $this->setRoles();
    }

    public function getFamilyName()
    {
        return $this->familyName;
    }

    public function getGivenName()
    {
        return $this->givenName;
    }

    public function getMechanism()
    {
        return $this->mechanism;
    }

    public function getModel()
    {
        return $this->account;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return false === $this->locked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true === $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return;
    }

    public function serialize()
    {
        return serialize([
            $this->familyName,
            $this->givenName,
            $this->password,
            $this->roles,
            $this->salt,
            $this->username,
            $this->locked,
            $this->enabled,
            $this->mechanism
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->familyName,
            $this->givenName,
            $this->password,
            $this->roles,
            $this->salt,
            $this->username,
            $this->locked,
            $this->enabled,
            $this->mechanism
        ) = unserialize($serialized);
    }

    private function setRoles()
    {
        $roles = (array) $this->account->get('roles');
        foreach ($roles as $role) {
            $role = strtoupper($role);
            if (0 === stripos($role, 'role_')) {
                $role = str_replace('ROLE_', '', $role);
            }
            $this->roles[] = sprintf('ROLE_APPLICATION\%s', $role);
        }
    }
}
