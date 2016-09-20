<?php

namespace AppBundle\Security\Authenticator;

use AppBundle\Security\Auth\AuthGeneratorManager;
use AppBundle\Security\User\CustomerProvider;
use As3\Modlr\Api\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\HttpUtils;

class CustomerAuthenticator extends AbstractCoreAuthenticator
{
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const REALM    = 'realm';

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
            throw new BadCredentialsException('The presented credentials cannot be empty.');
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
        return $userProvider->loadUserByUsername(json_encode([
            self::USERNAME => $credentials[self::USERNAME],
            self::REALM    => $credentials[self::REALM],
        ]));
    }

    /**
     * {@inheritdoc}
     */
    protected function extractCredentials(Request $request)
    {
        $userField  = self::USERNAME;
        $passField  = self::PASSWORD;
        $realmField = self::REALM;

        switch ($request->getMethod()) {
            case 'GET':
                return [
                    $userField  => $request->query->get($userField),
                    $passField  => $request->query->get($passField),
                    $realmField => $request->query->get($realmField),
                ];
            case 'POST':
                if (0 === stripos($request->headers->get('content-type'), 'application/json')) {
                    // JSON request.
                    $payload = @json_decode($request->getContent(), true);
                    return [
                        $userField   => isset($payload[$userField])  ? $payload[$userField]  : null,
                        $passField   => isset($payload[$passField])  ? $payload[$passField]  : null,
                        $realmField  => isset($payload[$realmField]) ? $payload[$realmField] : null,
                    ];
                }
                // Treat as standard x-www-form-urlencoded.
                return [
                    $userField  => $request->request->get($userField),
                    $passField  => $request->request->get($passField),
                    $realmField => $request->query->get($realmField),
                ];
                // Could support more post options - like XML? Shyeah, right...
            default:
                return [
                    $userField  => null,
                    $passField  => null,
                    $realmField => null,
                ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->authManager->createResponseFor($token->getUser());
    }
}
