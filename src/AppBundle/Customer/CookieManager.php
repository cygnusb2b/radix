<?php

namespace AppBundle\Customer;

use AppBundle\Customer\Cookies\CustomerSessionCookie;
use AppBundle\Customer\Cookies\CustomerVisitorCookie;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handles customer cookie management.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CookieManager
{
    const VISITOR_COOKIE = 'radix-cv';
    const SESSION_COOKIE = 'radix-cs';
    const VISITOR_EXPIRE = 63072000; # 2 years
    const SESSION_EXPIRE = 86400; # 24 hours

    /**
     * @var array
     */
    private $allowedTypes = [
        'customer-account'  => true,
        'customer-identity' => true,
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
     * Creates the cookies for a customer.
     *
     * @param   Model   $customer
     * @return  CustomerCookie[]
     * @throws  \InvalidArgumentException
     */
    public function createCookiesFor(Model $customer)
    {
        if (!isset($this->allowedTypes[$customer->getType()])) {
            throw new \InvalidArgumentException('The model type is not supported as a customer cookie.');
        }
        return [
            new CustomerCookie(self::VISITOR_COOKIE, self::VISITOR_EXPIRE, $customer->getId(), $customer->getType()),
            new CustomerCookie(self::SESSION_COOKIE, self::SESSION_EXPIRE, $customer->getId(), $customer->getType()),
        ];
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
        if (false === HelperUtility::isMongoIdFormat($data['id']) || !isset($this->$allowedTypes[$data['type']])) {
            return false;
        }
        return new CustomerCookie($name, $expires, $data['id'], $data['type']);
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
