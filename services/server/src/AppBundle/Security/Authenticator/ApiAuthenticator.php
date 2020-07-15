<?php

namespace AppBundle\Security\Authenticator;

use AppBundle\Security\JWT\JWTGeneratorManager;
use As3\Modlr\Api\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\HttpUtils;

class ApiAuthenticator extends AbstractCoreAuthenticator
{
    /**
     * @var JWTGeneratorManager
     */
    private $jwtManager;

    /**
     * @param   AdapterInterface    $adapter
     * @param   HttpUtils           $httpUtils
     * @param   JWTGeneratorManager $jwtManager
     */
    public function __construct(AdapterInterface $adapter, HttpUtils $httpUtils, JWTGeneratorManager $jwtManager)
    {
        parent::__construct($adapter, $httpUtils);
        $this->jwtManager = $jwtManager;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // No credential check is needed in this case, because the act of extracting and verifying the JWT (and find the user) is considered authenticated.
        return true;
    }

    /**
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // Let the request continue, since this is an API call.
        return;
    }

    /**
     * @param   string  $title
     * @param   string  $detail
     * @param   int     $httpCode
     */
    protected function createErrorResponse($title, $detail, $httpCode)
    {
        $response = parent::createErrorResponse($title, $detail, $httpCode);
        $response->headers->set('WWW-Authenticate', 'Bearer');
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function extractCredentials(Request $request)
    {
        $token = $this->jwtManager->extractFrom($request);
        if (empty($token)) {
            return;
        }
        return $token;
    }
}
