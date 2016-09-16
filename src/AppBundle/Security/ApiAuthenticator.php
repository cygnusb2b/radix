<?php

namespace AppBundle\Security;

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

use Lcobucci\JWT\Parser as JWTParser;
use Lcobucci\JWT\Signer\Hmac\Sha256 as JWTSigner;
use Lcobucci\JWT\Token as JWTToken;
use Lcobucci\JWT\ValidationData;

class ApiAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var AdapterInterface
     */
    private $manager;

    private $parser;

    private $signer;

    /**
     * @param   AdapterInterface    $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->parser  = new JWTParser();
        $this->signer  = new JWTSigner();
    }

    /**
     * Call on every matched request. If null is returned, the auth process stops.
     *
     * @param   Request     $request
     * @return  array
     */
    public function getCredentials(Request $request)
    {
        $token = $this->extractRawToken($request);
        if (empty($token)) {
            return;
        }
        return ['token' => $token];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        // @todo token parsing should be consolidated with generation.
        $token = $this->parser->parse($credentials['token']);

        $rules = new ValidationData();
        $rules->setIssuer('radix');
        if (false === $token->validate($rules)) {
            throw new AuthenticationException('Invalid token.');
        }
        if (false === $token->verify($this->signer, '_On6dvKqzugag6gcpU8wAhwEZ6ktu5EVFrgxlfkOurtQnMIFtfGpobqdUpYAGUzc')) {
            throw new AuthenticationException('Invalid token.');
        }

        $username = $token->getClaim('sub');
        if (empty($username)) {
            throw new AuthenticationException('Invalid user.');
        }

        return $userProvider->loadUserByUsername($username);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // No credential check is needed in this case, because the act of finding the application (via the public key) counts as authed.
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

    /**
     * Extracts a raw JWT token from the request.
     *
     * @param   Request     $request
     * @return  string|null
     */
    private function extractRawToken(Request $request)
    {
        $header = $request->headers->get('Authorization');
        if (0 !== strpos($header, 'Bearer')) {
            return null;
        }
        $raw = trim(str_replace('Bearer ', '', $request->headers->get('Authorization')));
        return (empty($raw)) ? null : $raw;
    }
}
