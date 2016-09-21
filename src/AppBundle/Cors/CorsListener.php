<?php

namespace AppBundle\Cors;

use AppBundle\Core\AccountManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsListener implements EventSubscriberInterface
{
    /**
     * Origins to always allow, regardless of application settings.
     *
     * @var array
     */
    private $alwaysAllow = [
        'http://radix.as3.io',
        'http://*.radix.as3.io',
    ];

    /**
     * @var CorsDefinition
     */
    private $definition;

    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @param   AccountManager  $manager
     */
    public function __construct(AccountManager $manager)
    {
        $this->manager    = $manager;
        $this->definition = $this->createCorsDefinition();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', -255], // Run last.
            ],
            KernelEvents::EXCEPTION => [
                ['onKernelException', -255], // Run last.
            ],
        ];
    }

    /**
     * Adds appropriate CORS headers to the response, when applicable.
     *
     * @param   FilterResponseEvent     $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        $context  = $this->manager->extractContextFrom($request);
        $origin   = $request->headers->get('Origin');


        if (false === $event->isMasterRequest() || 'application' !== $context || empty($origin)) {
            return;
        }

        if ('OPTIONS' === $request->getMethod()) {
            // Allow the pre-flight request to go through without checking application settings, as an app will not be defined.
            $origin = $request->headers->get('Origin');
            $this->handlePreFlightResponse($response, $origin);
            return;
        }

        $app     = $this->manager->getApplication();
        $origins = $this->alwaysAllow;
        if (null !== $app) {
            $origins = array_merge($origins, $app->get('allowedOrigins'));
        }

        foreach ($origins as $allowed) {
            $this->definition->addAllowedOrigin($allowed);
        }

        if (false === $this->definition->isOriginAllowed($origin)) {
            // Origin disallowed.
            return;
        }
        $this->handleResponse($response, $origin);
    }

    /**
     * Adds appropriate CORS headers to an exception response, when applicable.
     *
     * @param   FilterResponseEvent     $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();
        $context  = $this->manager->extractContextFrom($request);
        $origin   = $request->headers->get('Origin');
        if (false === $event->isMasterRequest() || 'application' !== $context || empty($origin)) {
            return;
        }



        if (false === $this->definition->isOriginAllowed($origin) && null !== $response) {
            // Origin disallowed.
            return;
        }
        $this->handleResponse($response, $origin);
    }

    /**
     * Creates the CORs definition.
     *
     * @return  CorsDefinition
     */
    private function createCorsDefinition()
    {
        $methods = ['GET', 'POST', 'OPTIONS', 'PATCH', 'DELETE'];
        $headers = ['content-type', 'authorization', 'x-radix-appid', 'x-requested-with'];
        return new CorsDefinition([], $methods, $headers, 86400, true);
    }

    /**
     * Appends the appropriate headers and properties to the response of a CORS preflight request.
     *
     * @param   Response    $response
     * @param   string      $origin
     */
    private function handlePreFlightResponse(Response $response, $origin)
    {
        $response->setStatusCode(200);
        $response->setContent(null);
        $response->setPublic();
        $response->setSharedMaxAge($this->definition->getMaxAge());
        foreach ($this->definition->getPreFlightHeaders($origin) as $key => $value) {
            $response->headers->set($key, $value);
        }
    }

    /**
     * Appends the appropriate headers to response requring CORS.
     *
     * @param   Response    $response
     * @param   string      $origin
     */
    private function handleResponse(Response $response, $origin)
    {
        foreach ($this->definition->getStandardHeaders($origin) as $key => $value) {
            $response->headers->set($key, $value);
        }
    }
}
