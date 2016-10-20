<?php

namespace AppBundle\Customer;

use AppBundle\Factory\Customer\CustomerAccountFactory as AccountFactory;
use AppBundle\Factory\Customer\CustomerIdentityFactory as IdentityFactory;
use AppBundle\Security\Auth\CustomerGenerator;
use AppBundle\Security\User\Customer;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @param   AccountFactory              $accountFactory
     * @param   IdentityFactory             $identityFactory
     * @param   TokenStorage                $tokenStorage
     * @param   CustomerGenerator           $authGenerator
     * @param   CookieManager               $cookieManager
     * @param   EventDispatcherInterface    $eventDispatcher
     */
    public function __construct(AccountFactory $accountFactory, IdentityFactory $identityFactory, TokenStorage $tokenStorage, CustomerGenerator $authGenerator, CookieManager $cookieManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->store           = $accountFactory->getStore();
        $this->accountFactory  = $accountFactory;
        $this->identityFactory = $identityFactory;
        $this->tokenStorage    = $tokenStorage;
        $this->authGenerator   = $authGenerator;
        $this->cookieManager   = $cookieManager;
        $this->eventDispatcher = $eventDispatcher;
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
            // @todo Remove sending the identity once backend tracking (Sapience/Olytics) has been integrated.
            $customer = $this->getActiveCustomer();
            $serialized['identity'] = (null === $customer) ? null : $customer->getId();
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
        $model = $this->getStore()->create('customer-account');
        $serialized = $this->authGenerator->getSerializer()->serialize($model);
        // @todo Remove sending the identity once backend tracking (Sapience/Olytics) has been integrated.
        $customer = $this->getActiveCustomer();
        $serialized['identity'] = (null === $customer) ? null : $customer->getId();
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
     * @return  AccountFactory
     */
    public function getAccountFactory()
    {
        return $this->accountFactory;
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
            $customer = $this->getStore()->find('customer-identity', $cookie->getIdentifier());
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
     * Gets the appropriate factory for the provided customer.
     *
     * @param   Model   $customer
     * @return  AccountFactory|IdentityFactory
     */
    public function getCustomerFactoryFor(Model $customer)
    {
        if ('customer-identity' === $customer->getType()) {
            return $this->getIdentityFactory();
        }
        return $this->getAccountFactory();
    }

    /**
     * @return  IdentityFactory
     */
    public function getIdentityFactory()
    {
        return $this->identityFactory;
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
     * @return  Store
     */
    public function getStore()
    {
        return $this->store;
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
     * Interactively logs in a customer account.
     *
     * @param   Model   $account
     * @return  Customer
     */
    public function login(Model $account)
    {
        $user  = new Customer($account);
        $token = new PostAuthenticationGuardToken(
            $user,
            'app',
            $user->getRoles()
        );
        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($this->cookieManager->getRequestStack()->getCurrentRequest(), $token);
        $this->eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);
        return $user;
    }

    /**
     * Serializes the currently logged in account.
     * Will use a default, empty model if not logged in.
     *
     * @return  array
     */
    public function serializeLoggedInAccount()
    {
        $user = $this->getSecurityUser();
        if ($user) {
            return $this->authGenerator->generateFor($user);
        }
        $model = $this->getStore()->create('customer-account');
        return $this->authGenerator->getSerializer()->serialize($model);
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
            $this->cookieManager->setCookiesTo($response, $customer, null !== $this->activeIdentity);
        }
        return $response;
    }

    /**
     * Updates or creates an identity (unsaved) for the provided email address with provided attributes.
     *
     * @param   string  $emailAddress
     * @param   array   $attributes
     * @return  Model|null
     */
    public function upsertIdentityFor($emailAddress, array $attributes = [])
    {
        if (empty($emailAddress)) {
            $identity = $this->getActiveIdentity();
            if (null === $identity) {
                return;
            }

            // Determine if this can update the identity.
            $session = $this->cookieManager->getSessionCookie();
            if (null !== $session && $session->getIdentifier() === $identity->getId()) {
                $this->identityFactory->apply($identity, $attributes);
                return $identity;
            }
            return;
        }

        $identity = $this->getStore()->findQuery('customer-identity', ['primaryEmail' => $emailAddress])->getSingleResult();
        if (null === $identity) {
            // No identity found. Create.
            $identity = $this->identityFactory->create($attributes);
        } else {
            // Update the existing identity.
            $this->identityFactory->apply($identity, $attributes);
        }
        return $identity;
    }
}
