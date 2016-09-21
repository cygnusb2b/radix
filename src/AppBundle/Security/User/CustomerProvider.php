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
     * Attempts to find a customer using password credentials (email/username).
     *
     * @param   string  $emailOrUsername
     * @return  Model
     * @throws  BadCredentialsException|UsernameNotFoundException
     */
    public function findViaPasswordCredentials($emailOrUsername)
    {
        $emailOrUsername = trim($emailOrUsername);
        if (empty($emailOrUsername)) {
            throw new BadCredentialsException('The presented username/email cannot be empty.');
        }

        // Try email address
        $criteria = [
            'value'    => strtolower($emailOrUsername),
            'verification.verified' => true,
        ];
        $email = $this->store->findQuery('customer-email', $criteria)->getSingleResult();
        if (null !== $email && null !== $email->get('account') && false === $email->get('account')->get('deleted')) {
            // Valid customer.
            return $email->get('account');
        }

        // Try username
        $criteria = [
            'credentials.password.username' => $emailOrUsername,
        ];
        $customer = $this->store->findQuery('customer-account', $criteria)->getSingleResult();
        if (null !== $customer && false === $customer->get('deleted')) {
            return $customer;
        }
        throw new UsernameNotFoundException('Unable to retrieve customer via email or username');
    }

    /**
     * @return  Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($customerId)
    {
        if (0 === preg_match('/^[a-f0-9]{24}$/', $customerId)) {
            throw new BadCredentialsException('The provided customer account identifier is invalid.');
        }

        try {
            $customer = $this->store->find('customer-account', $customerId);
            if (null === $customer || true === $customer->get('deleted')) {
                throw new UsernameNotFoundException('No user found.');
            }
            return new Customer($customer);
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
