<?php

namespace AppBundle\Security\User;

use As3\Modlr\Store\Store;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AccountProvider implements UserProviderInterface
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
     * Attempts to find an account using password credentials (email/username).
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

        // Try username
        $criteria = [
            'credentials.password.username' => $emailOrUsername,
        ];
        $account = $this->store->findQuery('identity-account', $criteria)->getSingleResult();
        if (null !== $account && false === $account->get('deleted')) {
            return $account;
        }

        // Try email address
        $criteria = [
            'value'    => strtolower($emailOrUsername),
            'verification.verified' => true,
        ];
        $email = $this->store->findQuery('identity-account-email', $criteria)->getSingleResult();
        if (null !== $email && null !== $email->get('account') && false === $email->get('account')->get('deleted')) {
            // Valid account.
            return $email->get('account');
        }



        // Determine if this is an email awaiting verification.
        $criteria = [
            'value'    => strtolower($emailOrUsername),
            'verification.verified' => false,
        ];
        $email = $this->store->findQuery('identity-account-email', $criteria)->getSingleResult();
        if (null !== $email && null !== $email->get('account')) {
            // Currently pending email verification.
            throw new CustomUserMessageAuthenticationException('This account is awaiting email verificaton. Please check your email and click the verification link.');
        }
        throw new UsernameNotFoundException('No account found for the provided email address or username');
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
    public function loadUserByUsername($accountId)
    {
        if (0 === preg_match('/^[a-f0-9]{24}$/', $accountId)) {
            throw new BadCredentialsException('The provided account identifier is invalid.');
        }

        try {
            $account = $this->store->find('identity-account', $accountId);
            if (null === $account || true === $account->get('deleted')) {
                throw new UsernameNotFoundException('No user found.');
            }
            return new Account($account);
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
        return 'AppBundle\Security\User\Account' === $class;
    }
}
