<?php

namespace AppBundle\Security\User;

use As3\Modlr\Store\Store;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CoreUserProvider implements UserProviderInterface
{
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
        $username = trim($username);
        if (empty($username)) {
            throw new BadCredentialsException('The presented username cannot be empty.');
        }

        $criteria  = ['email' => $username];

        try {
            $model = $this->store->findQuery('core-user', $criteria)->getSingleResult();
            if (null === $model) {
                throw new UsernameNotFoundException('No user found.');
            }
            return new CoreUser($model);
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
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return 'AppBundle\Security\User\CoreUser' === $class;
    }
}
