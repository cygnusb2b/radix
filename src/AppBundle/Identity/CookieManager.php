<?php

namespace AppBundle\Identity;

use AppBundle\Core\AccountManager;
use AppBundle\Utility\HelperUtility;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles identity cookie management.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CookieManager
{
    const VISITOR_COOKIE = '__radix-cv';
    const SESSION_COOKIE = '__radix-cs';
    const VISITOR_EXPIRE = 63072000; # 2 years
    const SESSION_EXPIRE = 86400; # 24 hours

    /**
     * @var array
     */
    private $allowedTypes = [
        'identity-account'  => true,
        'identity-internal' => true,
        'identity-external' => true,
    ];

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param   RequestStack    $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Creates the cookies for a identity.
     *
     * @param   Model   $identity
     * @param   bool    $identitySet    Whether the identity was intentionally set.
     * @return  IdentityCookie[]
     * @throws  \InvalidArgumentException
     */
    public function createCookiesFor(Model $identity, $identitySet = false)
    {
        if (!isset($this->allowedTypes[$identity->getType()])) {
            throw new \InvalidArgumentException('The model type is not supported as an identity cookie.');
        }

        $cookies = [];

        if ('identity-account' === $identity->getType()) {
            $cookies = [
                new IdentityCookie(self::VISITOR_COOKIE, self::VISITOR_EXPIRE, $identity->getId(), $identity->getType()),
                new IdentityCookie(self::SESSION_COOKIE, self::SESSION_EXPIRE, $identity->getId(), $identity->getType()),
            ];
        } else {
            if (true == $identitySet) {
                // Identity was set by the application (not from a previous session cookie).
                $cookies = [
                    new IdentityCookie(self::VISITOR_COOKIE, self::VISITOR_EXPIRE, $identity->getId(), $identity->getType()),
                    new IdentityCookie(self::SESSION_COOKIE, self::SESSION_EXPIRE, $identity->getId(), $identity->getType()),
                ];
            } else {
                $cookies[] = new IdentityCookie(self::VISITOR_COOKIE, self::VISITOR_EXPIRE, $identity->getId(), $identity->getType());

                $session = $this->getSessionCookie();
                if (null !== $session) {
                    // Session cookie found.
                    if ($session->getIdentifier() === $identity->getId()) {
                        // Session matches identity. Renew the session.
                        $cookies[] = new IdentityCookie(self::SESSION_COOKIE, self::SESSION_EXPIRE, $identity->getId(), $identity->getType());
                    } else {
                        // Flag that the session should be destroyed.
                        $this->requestStack->getCurrentRequest()->attributes->set('destroySessionCookie', true);
                    }
                }
            }
        }
        return $cookies;
    }

    /**
     * Destroys any customer cookies found in the provided response.
     *
     * @param   Response $response
     * @return  Response
     */
    public function destroyCookiesIn(Response $response)
    {
        foreach ($this->getCookieNames() as $name) {
            $response->headers->clearCookie($name, AccountManager::APP_PATH);
        }
        return $response;
    }

    /**
     * Destroys the session cookie.
     *
     * @param   Response $response
     * @return  Response
     */
    public function destroySessionCookie(Response $response)
    {
        $response->headers->clearCookie(self::SESSION_COOKIE, AccountManager::APP_PATH);
    }

    /**
     * Creates the cookie names used by the identity.
     *
     * @return  array
     */
    public function getCookieNames()
    {
        return [
            self::VISITOR_COOKIE,
            self::SESSION_COOKIE,
        ];
    }

    /**
     * @return  RequestStack
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * Gets a session cookie (if present/valid) from a request (or the current request if not specified).
     *
     * @param   Request|null    $request
     * @return  CustomerSessionCookie|null
     */
    public function getSessionCookie(Request $request = null)
    {
        $request = $request ?: $this->getCurrentRequest();
        if (null === $request) {
            return;
        }
        return $this->createFromRequest(self::SESSION_COOKIE, self::SESSION_EXPIRE, $request);
    }

    /**
     * Gets a visitor cookie (if present/valid) from a request (or the current request if not specified).
     *
     * @param   Request|null    $request
     * @return  CustomerVisitorCookie|null
     */
    public function getVisitorCookie(Request $request = null)
    {
        $request = $request ?: $this->getCurrentRequest();
        if (null === $request) {
            return;
        }
        return $this->createFromRequest(self::VISITOR_COOKIE, self::VISITOR_EXPIRE, $request);
    }

    /**
     * Sets cookies to the response for the provided identity.
     *
     * @param   Response    $response
     * @param   Model       $identity
     * @param   bool        $identitySet    Whether the identity was intentionally set.
     * @return  Response
     */
    public function setCookiesTo(Response $response, Model $identity, $identitySet = false)
    {
        $cookies = $this->createCookiesFor($identity, $identitySet);
        foreach ($cookies as $instance) {
            $response->headers->setCookie($instance->toCookie());;
        }
        return $response;
    }

    /**
     * Creates a cookie instance from a request object.
     *
     * @param   string  $value
     * @return  self|null
     */
    private function createFromRequest($name, $expires, Request $request)
    {
        $value = $request->cookies->get($name);
        if (empty($value)) {
            return;
        }
        $data = @json_decode($value, true);
        if (!isset($data['id']) || !isset($data['type'])) {
            return;
        }
        if (false === HelperUtility::isMongoIdFormat($data['id']) || !isset($this->allowedTypes[$data['type']])) {
            return false;
        }
        return new IdentityCookie($name, $expires, $data['id'], $data['type']);
    }

    /**
     * Gets the current request from the stack, if present.
     *
     * @return  Request|null
     */
    private function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }
}
