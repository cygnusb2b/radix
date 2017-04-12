<?php

namespace AppBundle\Identity;

use AppBundle\Factory\Identity\AbstractIdentityFactory;
use AppBundle\Security\Auth\AccountGenerator;
use AppBundle\Security\User\Account;
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
 * Common identity management functions.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class IdentityManager
{
    /**
     * @var Model|null
     */
    private $activeIdentity;

    /**
     * @var AccountGenerator
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
     * @var AbstractIdentityFactory
     */
    private $factories;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @param   TokenStorage                $tokenStorage
     * @param   AccountGenerator            $authGenerator
     * @param   CookieManager               $cookieManager
     * @param   EventDispatcherInterface    $eventDispatcher
     */
    public function __construct(TokenStorage $tokenStorage, AccountGenerator $authGenerator, CookieManager $cookieManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->tokenStorage    = $tokenStorage;
        $this->authGenerator   = $authGenerator;
        $this->cookieManager   = $cookieManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param   AbstractIdentityFactory     $factory
     * @return  self
     */
    public function addIdentityFactory(AbstractIdentityFactory $factory)
    {
        $this->store = $factory->getStore();
        $this->factories[$factory->getSupportsType()] = $factory;
        return $this;
    }

    /**
     * Creates a security auth response for the current identity account (or the default).
     *
     * @return  JsonResponse
     */
    public function createAuthResponse()
    {
        $user = $this->getSecurityUser();
        if ($user) {
            $serialized = $this->authGenerator->generateFor($user);
            $serialized = $this->appendIdentityId($serialized);
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
        $model = $this->getStore()->create('identity-account');
        $serialized = $this->authGenerator->serializeModel($model);
        $serialized = $this->appendIdentityId($serialized);
        return new JsonResponse($serialized);
    }

    /**
     * Creates cookie instances for the provided identity.
     *
     * @param   Model   $identity
     * @return  IdentityCookie
     */
    public function createCookiesFor(Model $identity)
    {
        return $this->cookieManager->createCookiesFor($identity);
    }

    /**
     * Destroys any identity cookies found in the provided response.
     *
     * @param   Response $response
     * @return  Response
     */
    public function destroyCookiesIn(Response $response)
    {
        return $this->cookieManager->destroyCookiesIn($response);
    }

    /**
     * Gets the active (logged in) identity account, if present.
     *
     * @return  Model|null
     */
    public function getActiveAccount()
    {
        if (null === $user = $this->getSecurityUser()) {
            return;
        }
        $model = $user->getModel();
        if ('identity-account' !== $model->getType()) {
            return;
        }
        return $model;
    }

    /**
     * Gets the active identity, if present.
     * Will return in the following order:
     * 1. If account logged in, will return account.
     * 2. If a specifically set identity is found, will return the set value.
     * 3. If an identity visitor cookie is found, will return the associated identity.
     * Invalid cookies, exceptions, and deleted identities will trigger a null response.
     *
     * @return  Model|null
     */
    public function getActiveIdentity()
    {
        if (true === $this->isAccountLoggedIn()) {
            return $this->getActiveAccount();
        }

        if (null !== $this->activeIdentity) {
            return $this->activeIdentity;
        }

        $cookie = $this->cookieManager->getVisitorCookie();
        if (null === $cookie || null === $cookie->getType()) {
            return;
        }

        try {
            $identity = $this->getStore()->find('identity', $cookie->getIdentifier());
        } catch (\Exception $e) {
            return;
        }
        return true === $identity->get('deleted') ? null : $identity;
    }

    /**
     * Gets the cookie names used by the identity.
     *
     * @return  array
     */
    public function getCookieNames()
    {
        return $this->cookieManager->getCookieNames();
    }

    /**
     * @param   string  $type
     * @return  AbstractIdentityFactory
     * @throws  \InvalidArgumentException
     */
    public function getIdentityFactoryForModel(Model $identity)
    {
        return $this->getIdentityFactoryFor($identity->getType());
    }

    /**
     * @param   string  $type
     * @return  AbstractIdentityFactory
     * @throws  \InvalidArgumentException
     */
    public function getIdentityFactoryFor($type)
    {
        if (isset($this->factories[$type])) {
            return $this->factories[$type];
        }
        throw new \InvalidArgumentException(sprintf('No identity factory found for type `%s`', $type));
    }

    /**
     * Gets the currently active symfony security user.
     *
     * @return  Account|null
     */
    public function getSecurityUser()
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }
        $user = $token->getUser();
        return $user instanceof Account ? $user : null;
    }

    /**
     * @return  Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Determines if an account is logged in.
     *
     * @return  bool
     */
    public function isAccountLoggedIn()
    {
        return null !== $this->getActiveAccount();
    }

    /**
     * Determines if an identity is present.
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
     * @return  Account
     */
    public function login(Model $account)
    {
        $user  = new Account($account);
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
     * Sets the active identity.
     * Note: this will set the appropriate identity cookies to the response.
     *
     * @param   Model   $model
     * @return  self
     * @throws  \InvalidArgumentException
     */
    public function setActiveIdentity(Model $model)
    {
        $allowed = ['identity-internal' => true, 'identity-external' => true, 'identity-account' => true];
        if (!isset($allowed[$model->getType()])) {
            throw new \InvalidArgumentException('The wrong model type was provided. Unable to set the active identity.');
        }
        $this->activeIdentity = $model;
        return $this;
    }

    /**
     * Sets the cookies for the active identity to the provided response.
     *
     * @param   Response $response
     * @return  Response
     */
    public function setCookiesTo(Response $response)
    {
        $identity = $this->getActiveIdentity();
        if (null !== $identity) {
            $this->cookieManager->setCookiesTo($response, $identity, null !== $this->activeIdentity);
        }
        return $response;
    }

    /**
     * Updates or creates an identity (unsaved) for the provided email address and attributes.
     *
     * @param   string  $emailAddress
     * @param   array   $attributes
     * @return  Model[]
     */
    public function upsertIdentitiesFor($emailAddress, array $attributes = [])
    {
        $emailAddress = ModelUtility::formatEmailAddress($emailAddress);

        if (empty($emailAddress)) {
            // No email address provided.
            $identity = $this->getActiveIdentity();
            if (null === $identity) {
                // No active identity. Create new.
                return [$this->getIdentityFactoryFor('identity-internal')->create($attributes)];
            }

            // Determine if this can update the identity.
            $session = $this->cookieManager->getSessionCookie();
            if (null !== $session && $session->getIdentifier() === $identity->getId()) {
                if ('identity-internal' === $identity->getType()) {
                    // Internal identity. Update
                    $this->getIdentityFactoryFor('identity-internal')->apply($identity, $attributes);
                    return [$identity];
                } else {
                    // @todo This could clone the current external identity and apply...
                    return [];
                }
            }
            return [];
        }

        // Email address was provided. Find all possible internal identities.
        $collection = $this->getStore()->findQuery('identity-internal', ['emails.value' => $emailAddress]);
        if (true === $collection->isEmpty()) {
            // No identities found. Create.
            return [$this->getIdentityFactoryFor('identity-internal')->create($attributes)];
        }

        // Identities found. Upsert each one.
        $identities = [];
        foreach ($collection as $identity) {
            $this->getIdentityFactoryFor('identity-internal')->apply($identity, $attributes);
            $identities[] = $identity;
        }
        return $identities;
    }

    /**
     * Appends the identity information to the serialized auth data.
     *
     * @param   array   $serialized
     * @return  array
     */
    private function appendIdentityId(array $serialized)
    {
        $identity = $this->getActiveIdentity();
        $serialized['identity'] = (null === $identity) ? null : $identity->getId();
        return $serialized;
    }
}
