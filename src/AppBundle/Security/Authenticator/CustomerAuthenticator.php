<?php

namespace AppBundle\Security\Authenticator;

use AppBundle\Utility\RequestUtility;
use AppBundle\Security\Auth\AuthGeneratorManager;
use AppBundle\Security\User\CustomerProvider;
use As3\Modlr\Api\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\HttpUtils;

class CustomerAuthenticator extends AbstractCoreAuthenticator
{
    const USERNAME = 'username';
    const PASSWORD = 'password';

    /**
     * @var AuthGeneratorManager
     */
    private $authManager;

    /**
     * @var EncoderFactory
     */
    private $encoderFactory;

    /**
     * @param   AdapterInterface        $adapter
     * @param   HttpUtils               $httpUtils
     * @param   EncoderFactory          $encoderFactory
     * @param   AuthGeneratorManager    $authManager
     */
    public function __construct(AdapterInterface $adapter, HttpUtils $httpUtils, EncoderFactory $encoderFactory, AuthGeneratorManager $authManager)
    {
        parent::__construct($adapter, $httpUtils);
        $this->authManager    = $authManager;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $passField = self::PASSWORD;
        if (empty($credentials[$passField])) {
            throw new BadCredentialsException('The presented password cannot be empty.');
        }

        // @todo Need to add support for the encoding mechanism.
        // $user->getMechanism() === 'platform' ... or instantiate a different user class if the mechanism is different?

        return $this->encoderFactory->getEncoder($user)->isPasswordValid(
            $user->getPassword(),
            $credentials[$passField],
            $user->getSalt()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!$userProvider instanceof CustomerProvider) {
            throw new AuthenticationServiceException('Improper customer provider passed.');
        }

        $emailOrUsername = isset($credentials[self::USERNAME]) ? $credentials[self::USERNAME] : null;
        $customer        = $userProvider->findViaPasswordCredentials($emailOrUsername);

        return $userProvider->loadUserByUsername($customer->getId());
    }

    /**
     * {@inheritdoc}
     */
    protected function extractCredentials(Request $request)
    {
        return RequestUtility::extractPayload($request);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->authManager->createResponseFor($token->getUser());
    }
}
