<?php

namespace AppBundle\Customer;

use AppBundle\Security\Auth\CustomerGenerator;
use AppBundle\Security\User\Customer;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Common customer management functions.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class CustomerManager
{
    /**
     * @var Model|null
     */
    private $activeIdentity;

    /**
     * @var CustomerGenerator
     */
    private $authGenerator;

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @param   Store               $store
     * @param   TokenStorage        $tokenStorage
     * @param   CustomerGenerator   $authGenerator
     * @param   CookieManager       $cookieManager
     */
    public function __construct(Store $store, TokenStorage $tokenStorage, CustomerGenerator $authGenerator, CookieManager $cookieManager)
    {
        $this->store         = $store;
        $this->tokenStorage  = $tokenStorage;
        $this->authGenerator = $authGenerator;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Creates a security auth response for the current customer account (or the default).
     *
     * @return  JsonResponse
     */
    public function createAuthResponse()
    {
        $user = $this->getSecurityUser();
        if ($user) {
            $serialized = $this->authGenerator->generateFor($user);
            return new JsonResponse($serialized);
        }
        return $this->createDefaultAuthResponse();
    }

    /**
     * Creates the security auth response (e.g. the non logged-in response).
     *
     * @return  JsonResponse
     */
    public function createDefaultAuthResponse()
    {
        $model = $this->store->create('customer-account');
        $serialized = $this->authGenerator->getSerializer()->serialize($model);
        return new JsonResponse($serialized);
    }

    /**
     * Creates cookie instances for the provided customer.
     *
     * @param   Model   $customer
     * @return  Customer\Cookies\AbstractCookie[]
     */
    public function createCookiesFor(Model $customer)
    {
        return $this->cookieManager->createCookiesFor($customer);
    }

    /**
     * Destroys any customer cookies found in the provided response.
     *
     * @param   Response $response
     * @return  Response
     */
    public function destroyCookiesIn(Response $response)
    {
        return $this->cookieManager->destroyCookiesIn($response);
    }

    /**
     * Gets the active (logged in) customer account, if present.
     *
     * @return  Model|null
     */
    public function getActiveAccount()
    {
        if (null === $user = $this->getSecurityUser()) {
            return;
        }
        $model = $user->getModel();
        if ('customer-account' !== $model->getType()) {
            return;
        }
        return $model;
    }

    /**
     * Gets the active customer identity (if not set, tries visitor cookie), if present.
     *
     * @return  Model|null
     */
    public function getActiveIdentity()
    {
        if (true === $this->isAccountLoggedIn()) {
            return;
        }

        if (null !== $this->activeIdentity) {
            return $this->activeIdentity;
        }

        $cookie = $this->cookieManager->getVisitorCookie();
        if (null === $cookie || 'customer-identity' !== $cookie->getType()) {
            return;
        }

        try {
            $customer = $this->store->find('customer-identity', $cookie->getIdentifier());
        } catch (\Exception $e) {
            return;
        }
        return true === $customer->get('deleted') ? null : $customer;
    }

    /**
     * Gets the active customer (account or identity), if present.
     *
     * @return  Model|null
     */
    public function getActiveCustomer()
    {
        if (true === $this->isAccountLoggedIn()) {
            return $this->getActiveAccount();
        }
        if (true === $this->isIdentityPresent()) {
            return $this->getActiveIdentity();
        }
    }

    /**
     * Gets the cookie names used by the customer.
     *
     * @return  array
     */
    public function getCookieNames()
    {
        return $this->cookieManager->getCookieNames();
    }

    /**
     * Gets the currently active symfony security user.
     *
     * @return  \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getSecurityUser()
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }
        $user = $token->getUser();
        return $user instanceof Customer ? $user : null;
    }

    /**
     * Determines if a customer account is logged in.
     *
     * @return  bool
     */
    public function isAccountLoggedIn()
    {
        return null !== $this->getActiveAccount();
    }

    /**
     * Determines if a customer of any type is present.
     *
     * @return  bool
     */
    public function isCustomerPresent()
    {
        return null !== $this->getActiveCustomer();
    }

    /**
     * Determines if a customer identity is present.
     *
     * @return  bool
     */
    public function isIdentityPresent()
    {
        return null !== $this->getActiveIdentity();
    }

    /**
     * Sets the active identity.
     * Note: this will set the appropriate identity cookies to the response, if a customer account is not logged in.
     *
     * @param   Model   $model
     * @return  self
     * @throws  \InvalidArgumentException
     */
    public function setActiveIdentity(Model $model)
    {
        if ('customer-identity' !== $model->getType()) {
            throw new \InvalidArgumentException('The wrong model type was provided. Unable to set the active customer identity.');
        }
        $this->activeIdentity = $model;
        return $this;
    }

    /**
     * Sets the cookies for the active customer to the provided response.
     *
     * @param   Response $response
     * @return  Response
     */
    public function setCookiesTo(Response $response)
    {
        if (null !== $customer = $this->getActiveCustomer()) {
            $this->cookieManager->setCookiesTo($response, $customer);
        }
        return $response;
    }
}
