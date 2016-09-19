<?php

namespace AppBundle\Security\User;

use \Serializable;
use As3\Modlr\Models\Model;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class Customer implements AdvancedUserInterface, Serializable
{
    private $authModel;

    private $customerModel;

    private $familyName;

    private $locked;

    private $enabled;

    private $givenName;

    private $password;

    private $roles = [];

    private $salt;

    private $username;

    public function __construct(Model $authModel)
    {
        $this->authModel     = $authModel;
        $this->customerModel = $authModel->get('account');

        if (null === $this->customerModel) {
            throw new \RuntimeException('No customer found on customer auth model.');
        }

        $this->familyName = $this->customerModel->get('familyName');
        $this->givenName  = $this->customerModel->get('givenName');
        $this->password   = $this->authModel->get('password');
        $this->salt       = $this->authModel->get('salt');
        $this->username   = json_encode([
            'username' => $this->authModel->get('username'),
            'realm' => $this->authModel->get('realm')->getId()
        ]);

        $this->locked  = $this->authModel->get('locked');
        $this->enabled = $this->authModel->get('enabled');

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

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Gets the customer auth model for this user instance.
     *
     * @return  Model
     */
    public function getAuthModel()
    {
        return $this->authModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Gets the application public access keys available to this user.
     *
     * @return  array
     */
    public function getPublicKeys()
    {
        return $this->publicKeys;
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
        foreach ($this->authModel->get('roles') as $role) {
            $role = strtoupper($role);
            if (0 === stripos($role, 'role_')) {
                $role = str_replace('ROLE_', '', $role);
            }
            $this->roles[] = sprintf('ROLE_APPLICATION\%s', $role);
        }
    }
}
