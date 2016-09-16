<?php

namespace AppBundle\Security\Authenticator;

use AppBundle\Core\AccountManager;
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

class ApplicationAuthenticator extends AbstractGuardAuthenticator
{
    const PUBLIC_KEY_PARAM = 'x-radix-appid';

    private $manager;

    private $adapter;

    public function __construct(AdapterInterface $adapter, AccountManager $manager)
    {
        $this->adapter = $adapter;
        $this->manager = $manager;
    }

    /**
     * Call on every matched request. If null is returned, the auth process stops.
     *
     * @param   Request     $request
     * @return  array
     */
    public function getCredentials(Request $request)
    {
        $param = self::PUBLIC_KEY_PARAM;

        $values = [
            'header'  => $request->headers->get($param),
            'request' => $request->query->get($param),
        ];

        $publicKey = null;
        foreach ($values as $value) {
            if (!empty($value)) {
                $publicKey = $value;
                break;
            }
        }
        if (empty($publicKey)) {
            return;
        }
        return ['token' => $publicKey];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $publicKey = $credentials['token'];

        try {
            $application = $this->adapter->getStore()->findQuery('core-application', ['publicKey' => $publicKey])->getSingleResult();
            if (null === $application) {
                return;
            }
            // Set the application to the manager.
            $this->manager->setApplication($application);

            // Need to return a UserInterface object.
            // Since this is an application, and only needs the public key to find a model, we can return an empty, built-in user object.
            return new User($application->get('key'), $application->get('publicKey'));
        } catch (\Exception $e) {
            throw new AuthenticationServiceException('', 0, $e);
        }
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
        $response->setJson($error);
        return $response;
    }
}
