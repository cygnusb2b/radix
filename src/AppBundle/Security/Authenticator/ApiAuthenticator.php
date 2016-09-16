<?php

namespace AppBundle\Security\Authenticator;

use AppBundle\Security\JWT\JWTGeneratorManager;
use As3\Modlr\Api\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var JWTGeneratorManager
     */
    private $jwtManager;

    /**
     * @param   AdapterInterface    $adapter
     */
    public function __construct(AdapterInterface $adapter, JWTGeneratorManager $jwtManager)
    {
        $this->adapter    = $adapter;
        $this->jwtManager = $jwtManager;
    }

    /**
     * Call on every matched request. If null is returned, the auth process stops.
     *
     * @param   Request     $request
     * @return  array
     */
    public function getCredentials(Request $request)
    {
        $token = $this->jwtManager->extractFrom($request);
        if (empty($token)) {
            return;
        }
        return $token;
    }

    /**
     * Gets the user from the database.
     *
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token    = $this->jwtManager->parseFor('core-user', $credentials);
        $username = $token->getClaim('sub');

        if (empty($username)) {
            throw new AuthenticationException('Invalid user.');
        }
        return $userProvider->loadUserByUsername($username);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // No credential check is needed in this case, because the act of extracting and verifying the JWT (and find the user) is considered authenticated.
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // Let the request continue
        return;
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
        return false;
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
        $response->headers->set('WWW-Authenticate', 'Bearer');
        $response->setJson($error);
        return $response;
    }
}
