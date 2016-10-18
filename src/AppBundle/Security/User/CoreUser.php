<?php

namespace AppBundle\Security\User;

use \Serializable;
use As3\Modlr\Models\Model;
use Symfony\Component\Security\Core\User\UserInterface;

class CoreUser implements UserInterface, Serializable
{
    private $availableApps = [];

    private $model;

    private $familyName;

    private $givenName;

    private $password;

    private $publicKeys = [];

    private $roles = [];

    private $salt;

    private $username;

    public function __construct(Model $model)
    {
        $this->model      = $model;
        $this->familyName = $model->get('familyName');
        $this->givenName  = $model->get('givenName');
        $this->password   = $model->get('password');
        $this->salt       = $model->get('salt');
        $this->username   = $model->get('email');

        $this->setRoles();
    }

    /**
     * Gets the applications available to this user.
     *
     * @return  array
     */
    public function getAvailableApps()
    {
        return $this->availableApps;
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
     * Gets the user model for this user instance.
     *
     * @return  Model
     */
    public function getModel()
    {
        return $this->model;
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
            $this->availableApps,
            $this->roles,
            $this->salt,
            $this->username,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->familyName,
            $this->givenName,
            $this->password,
            $this->availableApps,
            $this->roles,
            $this->salt,
            $this->username
        ) = unserialize($serialized);
    }

    private function setRoles()
    {
        $this->roles[] = 'ROLE_CORE\USER';
        foreach ($this->model->get('details') as $details) {
            $application = $details->get('application');
            $key = sprintf('%s:%s', $application->get('account')->get('key'), $application->get('key'));

            $this->availableApps[] = [
                'id'        => $key,
                'name'      => $application->get('name'),
                'fullName'  => sprintf('%s: %s', $application->get('account')->get('name'), $application->get('name')),
                'key'       => $application->get('publicKey'),
            ];

            foreach($details->get('roles') as $role) {
                $role = strtoupper($role);
                if (0 === stripos($role, 'role_')) {
                    $role = str_replace('ROLE_', '', $role);
                }
                $this->roles[] = sprintf('ROLE_%s\%s', strtoupper($key), $role);
            }
        }
    }
}
