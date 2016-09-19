<?php

namespace AppBundle\Core;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\HttpUtils;

class KernelSubscriber implements EventSubscriberInterface
{
    /**
     * Route names that do NOT need to check for the existence of the app id.
     * Some examples may include: the initial management login page, auth checks/submits, etc.
     *
     * @var array
     */
    private $excludeRoutes = [];

    /**
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @param   AccountManager  $manager
     */
    public function __construct(AccountManager $manager, HttpUtils $httpUtils)
    {
        $this->manager   = $manager;
        $this->httpUtils = $httpUtils;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['loadApplication', 7], // 7 Ensures that this runs right after the firewall.
            ],
            KernelEvents::RESPONSE => [
                ['appendKey']
            ],
        ];
    }

    public function appendKey(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->set(AccountManager::USING_PARAM, $this->manager->getCompositeKey());
    }

    /**
     * @param   string  $routeName
     * @return  self
     */
    public function addExcludeRoute($routeName)
    {
        $this->excludeRoutes[$routeName] = true;
        return $this;
    }

    /**
     * Ensures that an application has been set.
     *
     * @param   GetResponseEvent    $event
     * @throws  \RuntimeException
     */
    public function loadApplication(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$event->isMasterRequest() || false === $this->shouldProcess($request)) {
            return;
        }

        $param     = AccountManager::PUBLIC_KEY_PARAM;
        $publicKey = $this->extractPublicKey($request);

        if (empty($publicKey)) {
            // Attempt to find key in session.
            $publicKey = $request->getSession()->get($param);
        }

        if (empty($publicKey)) {
            $this->manager->allowDbOperations(false);
            return;
        }

        $application = $this->manager->retrieveByPublicKey($publicKey);
        if (null === $application) {
            $this->manager->allowDbOperations(false);
            return;
        }

        // Set the application model to the manager.
        $this->manager->setApplication($application);


        // Set the public key to the session.
        $request->getSession()->set($param, $publicKey);

        if (null !== $redirect = $this->getRedirectUrl($request)) {
            // Redirect.
            $event->setResponse(new RedirectResponse($redirect));
            return;
        }
    }

    private function getRedirectUrl(Request $request)
    {
        $param = AccountManager::PUBLIC_KEY_PARAM;
        if ('GET' !== $request->getMethod() || false === $request->query->has($param)) {
            return;
        }

        $request->query->remove($param);
        $new = Request::create($request->getPathInfo(), $request->getMethod(), $request->query->all(), $request->cookies->all(), [], $request->server->all());
        return $new->getUri();
    }

    /**
     * @param   Request     $request
     * @return  string|null
     */
    private function extractPublicKey(Request $request)
    {
        $param   = AccountManager::PUBLIC_KEY_PARAM;
        $values  = [
            'header' => $request->headers->get($param),
            'query'  => $request->query->get($param),
        ];

        $publicKey = null;
        foreach ($values as $value) {
            if (!empty($value)) {
                $publicKey = $value;
                break;
            }
        }
        return empty($publicKey) ? null : $publicKey;
    }

    private function shouldProcess(Request $request)
    {
        foreach ($this->excludeRoutes as $name => $enabled) {
            if (true === $this->httpUtils->checkRequestPath($request, $name)) {
                return false;
            }
        }
        return true;
    }
}
