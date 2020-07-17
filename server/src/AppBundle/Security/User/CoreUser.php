<?php

namespace AppBundle\Security\User;

use \Serializable;
use AppBundle\Core\AccountManager;
use AppBundle\Cors\CorsDefinition as Cors;
use As3\Modlr\Models\Model;
use Symfony\Component\Security\Core\User\UserInterface;

class CoreUser implements UserInterface, Serializable
{
    private $applications = [];

    private $identfier;

    private $model;

    private $origin;

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
        $this->identifier = $model->getId();
        $this->familyName = $model->get('familyName');
        $this->givenName  = $model->get('givenName');
        $this->password   = $model->get('password');
        $this->salt       = $model->get('salt');
        $this->username   = $model->get('email');
    }

    /**
     * Gets the applications available to this user.
     *
     * @return  array
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * @return  string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @return  string
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * Gets the user database id.
     *
     * @return  string
     */
    public function getIdentifier()
    {
        return $this->identifier;
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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->identifier,
            $this->familyName,
            $this->givenName,
            $this->password,
            $this->roles,
            $this->salt,
            $this->username,
        ]);
    }

    /**
     * Sets the request/auth origin.
     * Determines what applications/roles will be available to the user.
     *
     * @param   string  $origin
     * @return  self
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        $this->loadApplications();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->identifier,
            $this->familyName,
            $this->givenName,
            $this->password,
            $this->roles,
            $this->salt,
            $this->username
        ) = unserialize($serialized);
    }

    /**
     * Applies applications details for an allowed application.
     *
     * @param   Model   $application
     * @param   array   $roles
     */
    private function applyAppDetailsFor(Model $application, array $roles)
    {
        $key = sprintf('%s:%s', $application->get('account')->get('key'), $application->get('key'));

        $this->applications[] = [
            '_id'       => $application->getId(),
            'id'        => $key,
            'name'      => $application->get('name'),
            'fullName'  => sprintf('%s: %s', $application->get('account')->get('name'), $application->get('name')),
            'key'       => $application->get('publicKey'),
        ];

        foreach ($roles as $role) {
            $role = strtoupper($role);
            if (0 === stripos($role, 'role_')) {
                $role = str_replace('ROLE_', '', $role);
            }
            $this->roles[] = sprintf('ROLE_%s\%s', strtoupper($key), $role);
        }
    }

    /**
     * Loads all applications available to user, based on the user auth origin.
     *
     */
    private function loadApplications()
    {
        // Determine if a global origin was set.
        $global = false;
        foreach (AccountManager::getGlobalOrigins() as $origin) {
            if (true === Cors::isOriginMatch($this->origin, $origin)) {
                $global = true;
                break;
            }
        }

        foreach ($this->model->get('details') as $details) {
            $application = $details->get('application');
            if (null === $application) {
                continue;
            }
            if (true === $global) {
                // If global flag, allow all applications found for the user.
                $this->applyAppDetailsFor($application, $details->get('roles'));
                continue;
            }
            foreach ($application->get('allowedOrigins') as $origin) {
                if (true === Cors::isOriginMatch($this->origin, $origin)) {
                    // Limit applications to matched origins only.
                    $this->applyAppDetailsFor($application, $details->get('roles'));
                    continue;
                }
            }
        }

        if (!empty($this->roles)) {
            // Set standard user role.
            $this->roles[] = 'ROLE_CORE\USER';
        }
    }
}
