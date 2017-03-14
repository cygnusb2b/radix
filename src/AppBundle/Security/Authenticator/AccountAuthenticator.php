<?php

namespace AppBundle\Security\Authenticator;

use AppBundle\Identity\IdentityManager;
use AppBundle\Security\Encoder\LegacyEncoderManager;
use AppBundle\Security\User\AccountProvider;
use AppBundle\Utility\RequestUtility;
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

class AccountAuthenticator extends AbstractCoreAuthenticator
{
    const USERNAME = 'username';
    const PASSWORD = 'password';

    /**
     * @var IdentityManager
     */
    private $identityManager;

    /**
     * @var EncoderFactory
     */
    private $encoderFactory;

    /**
     * @var LegacyEncoderManager
     */
    private $legacyEncoders;

    /**
     * @param   AdapterInterface        $adapter
     * @param   HttpUtils               $httpUtils
     * @param   EncoderFactory          $encoderFactory
     * @param   IdentityManager         $identityManager
     * @param   LegacyEncoderManager    $legacyEncoders
     */
    public function __construct(AdapterInterface $adapter, HttpUtils $httpUtils, EncoderFactory $encoderFactory, IdentityManager $identityManager, LegacyEncoderManager $legacyEncoders)
    {
        parent::__construct($adapter, $httpUtils);
        $this->identityManager = $identityManager;
        $this->encoderFactory  = $encoderFactory;
        $this->legacyEncoders  = $legacyEncoders;
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

        $mechanism = $user->getMechanism();
        if (empty($mechanism) || LegacyEncoderManager::CORE_MECHANISM === $mechanism) {
            // Default password encoder.
            return $this->encoderFactory->getEncoder($user)->isPasswordValid(
                $user->getPassword(),
                $credentials[$passField],
                $user->getSalt()
            );
        }

        // Try an alternative/legacy encoding mechanism.
        if (null === $encoder = $this->legacyEncoders->getEncoder($mechanism)) {
            throw new AuthenticationServiceException(sprintf('The password mechanism `%s` is not registered.', $mechanism));
        }
        $valid = $encoder->isPasswordValid(
            $user->getPassword(),
            $credentials[$passField],
            $user->getSalt()
        );

        if (true === $valid) {
            // Update customer to core mechanism.
            $model   = $user->getModel();
            $encoded = $this->encoderFactory->getEncoder($user)->encodePassword($credentials[$passField], null);
            $model->get('credentials')->get('password')
                ->set('mechanism', LegacyEncoderManager::CORE_MECHANISM)
                ->set('value', $encoded)
                ->set('salt', null)
            ;
            $model->save();
        }
        return $valid;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (!$userProvider instanceof AccountProvider) {
            throw new AuthenticationServiceException('Improper customer provider passed.');
        }

        $emailOrUsername = isset($credentials[self::USERNAME]) ? $credentials[self::USERNAME] : null;
        $customer        = $userProvider->findViaPasswordCredentials($emailOrUsername);

        return $userProvider->loadUserByUsername($customer->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->identityManager->createAuthResponse();
    }

    /**
     * {@inheritdoc}
     */
    protected function extractCredentials(Request $request)
    {
        return RequestUtility::extractPayload($request);
    }
}
