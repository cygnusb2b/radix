<?php

namespace AppBundle\Security;

use AppBundle\Security\User\CoreUser;
use As3\Modlr\Api\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

use Lcobucci\JWT\Builder as JWTBuilder;
use Lcobucci\JWT\Signer\Hmac\Sha256 as JWTSigner;

class CoreUserAuthenticator extends AbstractGuardAuthenticator
{
    const USERNAME = 'username';
    const PASSWORD = 'password';

    /**
     * @param AdapterInterface
     */
    private $adapter;

    /**
     * @var EncoderFactory
     */
    private $encoderFactory;

    /**
     * @param   AdapterInterface    $adapter
     * @param   EncoderFactory      $encoderFactory
     */
    public function __construct(AdapterInterface $adapter, EncoderFactory $encoderFactory)
    {
        $this->adapter = $adapter;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Call on every matched request. If null is returned, the auth process stops.
     *
     * @param   Request     $request
     * @return  array
     */
    public function getCredentials(Request $request)
    {
        $userField = self::USERNAME;
        $passField = self::PASSWORD;

        switch ($request->getMethod()) {
            case 'GET':
                $credentials = [
                    $userField  => $request->query->get($userField),
                    $passField  => $request->query->get($passField),
                ];
                break;
            case 'POST':
                if (0 === stripos($request->headers->get('content-type'), 'application/json')) {
                    // JSON request.
                    $payload = @json_decode($request->getContent(), true);
                    $credentials = [
                        $userField  => isset($payload[$userField]) ? $payload[$userField] : null,
                        $passField  => isset($payload[$passField]) ? $payload[$passField] : null,
                    ];
                } else {
                    // Treat as standard x-www-form-urlencoded.
                    $credentials = [
                        $userField  => $request->request->get($userField),
                        $passField  => $request->request->get($passField),
                    ];
                }
                // Could support more post options - like XML? Shyeah, right...
                break;
            default:
                // Method not supported.
                return;
        }

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials[self::USERNAME]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $passField = self::PASSWORD;
        if (empty($credentials[$passField])) {
            throw new BadCredentialsException('The presented credentials cannot be empty.');
        }

        return $this->encoderFactory->getEncoder($user)->isPasswordValid(
            $user->getPassword(),
            $credentials[$passField],
            $user->getSalt()
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // @todo This should made into an auth object generator. @see Cyngus\ApplicationBundle\Auth
        $user     = $token->getUser();
        $model    = $user->getModel();
        $response = [
            'username'      => $user->getUserName(),
            'givenName'     => $user->getGivenName(),
            'familyName'    => $user->getFamilyName(),
            'roles'         => $user->getRoles(),
            'applications'  => $user->getPublicKeys(),
            'token'         => $this->createJwtToken($token),
        ];

        return new JsonResponse($response);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->createErrorResponse('Forbidden', $exception->getMessageKey(), 403);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->createErrorResponse('Unauthorized', 'Authentication required.', 401);
    }

    public function supportsRememberMe()
    {
        return true;
    }

    /**
     * @param   string  $title
     * @param   string  $detail
     * @param   int     $httpCode
     */
    private function createErrorResponse($title, $detail, $httpCode)
    {
        $error    = $this->adapter->getSerializer()->serializeError($title, $detail, $httpCode);
        $response = new JsonResponse(null, $httpCode);
        $response->setJson($error);
        return $response;
    }

    /**
     * @todo    This should move into an auth generator service and use the parameters.yml
     */
    private function createJwtToken(TokenInterface $token)
    {
        // Once this moves, will need to confirm that this a valid token. Can assume good here.

        $signer  = new JWTSigner();
        $builder = new JWTBuilder();

        // These params should come from parameters.yml.
        $ttl      = 86400;
        $secret   = '_On6dvKqzugag6gcpU8wAhwEZ6ktu5EVFrgxlfkOurtQnMIFtfGpobqdUpYAGUzc';
        $issuer   = 'radix';
        $audience = 'core-user';

        // Start the dance.
        $now     = time();
        $expires = time() + $ttl;

        $jwt = $builder
            ->setSubject($token->getUsername())
            ->setIssuer($issuer)
            ->setExpiration($expires)
            ->setIssuedAt($now)
            ->setAudience($audience)
            ->sign($signer, $secret)
            ->getToken()
        ;
        return (string) $jwt;
    }
}
