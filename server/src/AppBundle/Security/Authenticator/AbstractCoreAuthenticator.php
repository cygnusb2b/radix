<?php

namespace AppBundle\Security\Authenticator;

use As3\Modlr\Api\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\HttpUtils;

abstract class AbstractCoreAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * A list of route names that should check authetication.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @param   AdapterInterface    $adapter
     * @param   EncoderFactory      $encoderFactory
     */
    public function __construct(AdapterInterface $adapter, HttpUtils $httpUtils)
    {
        $this->adapter   = $adapter;
        $this->httpUtils = $httpUtils;
    }

    /**
     * Adds a route to check auth against.
     *
     * @param   string  $routeName
     * @return  self
     */
    public function addRoute($routeName)
    {
        $this->routes[$routeName] = true;
        return $this;
    }

    /**
     * Called on every request inside the configured firewall.
     * Will return null if authentication is not required.
     *
     * @param   Request     $request
     * @return  mixed|null
     */
    public function getCredentials(Request $request)
    {
        if (false === $this->requiresAuthentication($request)) {
            return;
        }
        return $this->extractCredentials($request);
    }

    /**
     * Sets/resets the authed routes.
     *
     * @see     addRoute()
     * @param   array   $routeNames
     * @return  self
     */
    public function setRoutes(array $routeNames)
    {
        $this->routes = [];
        foreach ($routeNames as $routeName) {
            $this->addRoute($routeName);
        }
        return $this;
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * Returns a JSON response.
     *
     * @param   Request                         $request
     * @param   AuthenticationException|null    $authException
     * @return  JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return $this->createErrorResponse('Unauthorized', 'Authentication required.', 401);
    }

    /**
     * Determines if remember me is supported (assuming it's configured on the firewall).
     *
     * @return  bool
     */
    public function supportsRememberMe()
    {
        return true;
    }

    /**
     * Fires when authentication fails.
     * Returns a JSON response.
     *
     * @param   Request                 $request
     * @param   AuthenticationException $exception
     * @return  JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->createErrorResponse('Forbidden', $exception->getMessageKey(), 403);
    }

    /**
     * @param   string  $title
     * @param   string  $detail
     * @param   int     $httpCode
     */
    protected function createErrorResponse($title, $detail, $httpCode)
    {
        $error    = $this->adapter->getSerializer()->serializeError($title, $detail, $httpCode);
        $response = new JsonResponse(null, $httpCode);
        $response->setJson($error);
        return $response;
    }

    /**
     * Extracts the credentials from the request.
     *
     * @param   Request  $request
     * @return  mixed
     */
    abstract protected function extractCredentials(Request $request);

    /**
     * Determines if an auth check is required.
     *
     * @param   Request     $request
     * @return  bool
     */
    protected function requiresAuthentication(Request $request)
    {
        foreach ($this->routes as $name => $enabled) {
            if (true === $this->httpUtils->checkRequestPath($request, $name)) {
                return true;
            }
        }
        return false;
    }
}
