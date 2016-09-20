<?php

namespace AppBundle\Security\User;

use As3\Modlr\Store\Store;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerProvider implements UserProviderInterface
{
    /**
     * @var string|null
     */
    private $realm;

    /**
     * @var Store
     */
    private $store;

    /**
     * @param   Store   $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $values = @json_decode($username, true);
        if (!is_array($values) || !isset($values['username']) || !isset($values['realm'])) {
            throw new AuthenticationServiceException('Unable to extract username and realm values.');
        }



        $this->setRealm($values['realm']);

        $username = trim($values['username']);
        if (empty($username)) {
            throw new BadCredentialsException('The presented username cannot be empty.');
        }



        if (0 === preg_match('/^[0-9a-f]{24}$/', $this->realm)) {
            throw new AuthenticationServiceException('An invalid authentication realm was specified.');
        }

        $criteria  = ['username' => $username, 'realm' => $this->realm];
        try {
            $model = $this->store->findQuery('customer-authentication', $criteria)->getSingleResult();
            if (null === $model) {
                throw new UsernameNotFoundException('No user found.');
            }
            return new Customer($model);
        } catch (\Exception $e) {
            if ($e instanceof UsernameNotFoundException) {
                throw $e;
            }
            throw new AuthenticationServiceException('An internal error occurred when retrieving the user.', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (false === $this->supportsClass(get_class($user))) {
            // Unsupported user class
            throw new UnsupportedUserException('The provided user object is not supported by this provider.');
        }
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Sets the authentication realm.
     *
     * @param   string  $realm
     * @return  self
     */
    public function setRealm($realm)
    {
        $this->realm = $realm;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return 'AppBundle\Security\User\Customer' === $class;
    }
}
