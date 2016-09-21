<?php

namespace AppBundle\Core;

use AppBundle\Cors\CorsDefinition;
use AppBundle\Exception\HttpFriendlySerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
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
     * @var HttpFriendlySerializer
     */
    private $serializer;

    /**
     * @param   AccountManager          $manager
     * @param   HttpUtils               $httpUtils
     * @param   HttpFriendlySerializer  $serializer
     */
    public function __construct(AccountManager $manager, HttpUtils $httpUtils, HttpFriendlySerializer $serializer)
    {
        $this->manager    = $manager;
        $this->httpUtils  = $httpUtils;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['loadApplication', 8], // 8 Ensures that this runs right before the firewall.
            ],
            KernelEvents::RESPONSE => [
                ['appendKey']
            ],
            KernelEvents::EXCEPTION => [
                ['onKernelException']
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
        $context = $this->manager->extractContextFrom($request);

        if (!$event->isMasterRequest() || false === $this->shouldProcess($request, $context)) {
            return;
        }

        if (true === $this->isPreflightRequest($request, $context)) {
            // Is a CORs pre-flight request. Let the process continue, but prevent db ops (as a safety);
            $this->manager->allowDbOperations(false);
            return;
        }

        $param        = AccountManager::PUBLIC_KEY_PARAM;
        $publicKey    = $this->manager->extractPublicKeyFrom($request);
        $sessionParam = $this->manager->getSessionKeyFor($request);

        if (empty($publicKey)) {
            $this->handleEmptyApp($context);
            return;
        }

        $application = $this->manager->retrieveByPublicKey($publicKey);
        if (null === $application) {
            $this->handleEmptyApp($context);
            return;
        }

        // Set the application model to the manager.
        $this->manager->setApplication($application);

        if (null !== $redirect = $this->getRedirectUrl($request)) {
            // Redirect.
            $event->setResponse(new RedirectResponse($redirect));
            return;
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();
        $context  = $this->manager->extractContextFrom($request);

        if (!$event->isMasterRequest() || false === $this->shouldProcess($request, $context)) {
            return;
        }

        $exception  = $event->getException();
        $status     = $this->serializer->extractStatusCode($exception);
        $serialized = $this->serializer->queueToJson([$exception]);

        $response = $response ?: new Response();

        $response->setStatusCode($status);
        $response->setContent($serialized);
        $response->headers->set('content-type', 'application/json');

        $event->setResponse($response);

        return $response;
    }

    /**
     * Gets a redirect url for the provided request, if applicable.
     *
     * @param   Request $request
     * @return  string|null
     */
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
     * Handles an empty application condition.
     *
     * @param   string  $context
     * @throws  \RuntimeException
     */
    private function handleEmptyApp($context)
    {
        $this->manager->allowDbOperations(false);
        if ('application' === $context) {
            throw new \RuntimeException('No application was defined or found. Operations terminated.');
        }
    }

    /**
     * Determines if this is a CORs pre-flight request.
     *
     * @param   Request $request
     * @param   string  $context
     * @return  bool
     */
    private function isPreflightRequest(Request $request, $context)
    {
        if ('application' !== $context) {
            return false;
        }
        $origin = $request->headers->get('Origin');
        return 'OPTIONS' === $request->getMethod() && !empty($origin);
    }

    /**
     * Determines if the subscriber should process data.
     *
     * @param   Request $request
     * @param   string  $context
     * @return  bool
     */
    private function shouldProcess(Request $request, $context)
    {
        if ('application' === $context) {
            return true;
        }
        foreach ($this->excludeRoutes as $name => $enabled) {
            if (true === $this->httpUtils->checkRequestPath($request, $name)) {
                return false;
            }
        }
        return true;
    }
}
