<?php

namespace AppBundle\Security\Authenticator;

use AppBundle\Security\Auth\AuthGeneratorManager;
use AppBundle\Utility\RequestUtility;
use As3\Modlr\Api\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\HttpUtils;

class CoreUserAuthenticator extends AbstractCoreAuthenticator
{
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const ORIGIN   = 'origin';

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
        $userField = self::USERNAME;
        if (empty($credentials[$userField]) || empty($credentials[$passField])) {
            throw new BadCredentialsException('The presented credentials cannot be empty.');
        }

        $valid = $this->encoderFactory->getEncoder($user)->isPasswordValid(
            $user->getPassword(),
            $credentials[$passField],
            $user->getSalt()
        );
        if (false === $valid) {
            return false;
        }

        // Set the authentication origin and determine if applications can be accessed.
        $user->setOrigin($credentials[self::ORIGIN]);
        if (empty($user->getApplications())) {
            throw new InsufficientAuthenticationException('No applications are available for this user.');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials[self::USERNAME]);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->authManager->createResponseFor($token->getUser());
    }

    /**
     * {@inheritdoc}
     */
    protected function extractCredentials(Request $request)
    {
        $payload = RequestUtility::extractPayload($request);
        $payload[self::ORIGIN] = $request->getSchemeAndHttpHost();
        return $payload;
    }
}
