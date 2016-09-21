<?php

namespace AppBundle\Security\User;

use \Serializable;
use As3\Modlr\Models\Model;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class Customer implements AdvancedUserInterface, Serializable
{
    private $customer;
    private $enabled = false;
    private $familyName;
    private $givenName;
    private $locked = true;
    private $password;
    private $roles = [];
    private $salt;
    private $username;

    public function __construct(Model $customer)
    {
        $this->customer = $customer;

        $this->familyName  = $customer->get('familyName');
        $this->givenName   = $customer->get('givenName');

        // @todo Will need to account for how social users get loaded here.
        // Or will (likely) need a new user class.

        $credentials = $customer->get('credentials');

        if (null !== $credentials && null !== $password = $credentials->get('password')) {
            $this->password   = $password->get('value');
            $this->salt       = $password->get('salt');
            $this->username   = $customer->getId();
        }

        if (null !== $settings = $customer->get('settings')) {
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

    public function getModel()
    {
        return $this->customer;
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
            $this->enabled
        ) = unserialize($serialized);
    }

    private function setRoles()
    {
        $roles = (array) $this->customer->get('roles');
        foreach ($roles as $role) {
            $role = strtoupper($role);
            if (0 === stripos($role, 'role_')) {
                $role = str_replace('ROLE_', '', $role);
            }
            $this->roles[] = sprintf('ROLE_APPLICATION\%s', $role);
        }
    }
}
