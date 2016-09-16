<?php

namespace AppBundle\Security\Authenticator;

use AppBundle\Security\Auth\AuthGeneratorManager;
use As3\Modlr\Api\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\HttpUtils;

class CoreUserAuthenticator extends AbstractCoreAuthenticator
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
        $userField = self::USERNAME;
        if (empty($credentials[$userField]) || empty($credentials[$passField])) {
            throw new BadCredentialsException('The presented credentials cannot be empty.');
        }

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
        return $userProvider->loadUserByUsername($credentials[self::USERNAME]);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $data = $this->authManager->generateFor($token->getUser());
        return new JsonResponse($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function extractCredentials(Request $request)
    {
        $userField = self::USERNAME;
        $passField = self::PASSWORD;

        switch ($request->getMethod()) {
            case 'GET':
                return [
                    $userField  => $request->query->get($userField),
                    $passField  => $request->query->get($passField),
                ];
            case 'POST':
                if (0 === stripos($request->headers->get('content-type'), 'application/json')) {
                    // JSON request.
                    $payload = @json_decode($request->getContent(), true);
                    return [
                        $userField  => isset($payload[$userField]) ? $payload[$userField] : null,
                        $passField  => isset($payload[$passField]) ? $payload[$passField] : null,
                    ];
                }
                // Treat as standard x-www-form-urlencoded.
                return [
                    $userField  => $request->request->get($userField),
                    $passField  => $request->request->get($passField),
                ];
                // Could support more post options - like XML? Shyeah, right...
            default:
                return [
                    $userField  => null,
                    $passField  => null,
                ];
        }
    }
}
