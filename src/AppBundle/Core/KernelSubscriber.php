<?php

namespace AppBundle\Core;

use AppBundle\Cors\CorsDefinition;
use AppBundle\Exception\HttpFriendlyException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
     * @var HttpUtils
     */
    private $httpUtils;

    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @var ApplicationQuery
     */
    private $query;

    /**
     * @var RedisCacheManager
     */
    private $redisManager;

    /**
     * @param   AccountManager          $manager
     * @param   ApplicationQuery        $query
     * @param   HttpUtils               $httpUtils
     * @param   RedisCacheManager       $cacheManager
     */
    public function __construct(AccountManager $manager, ApplicationQuery $query, HttpUtils $httpUtils, RedisCacheManager $redisManager)
    {
        $this->manager      = $manager;
        $this->query        = $query;
        $this->httpUtils    = $httpUtils;
        $this->redisManager = $redisManager;
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
        if (!$event->isMasterRequest()) {
            return;
        }
        $event->getResponse()->headers->set(AccountManager::USING_PARAM, $this->manager->getCompositeKey());
        $event->getResponse()->headers->set(AccountManager::BUILD_PARAM, $this->manager->getBuildKey());
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

        if (!$event->isMasterRequest()) {
            return;
        }

        if (true === $this->isPreflightRequest($request, $context)) {
            // Is a CORs pre-flight request. Let the process continue, but prevent db ops (as a safety);
            $this->manager->allowDbOperations(false);
            return;
        }

        $param     = AccountManager::PUBLIC_KEY_PARAM;
        $publicKey = $this->manager->extractPublicKeyFrom($request);

        if (empty($publicKey)) {
            $this->handleEmptyApp($context);
            return;
        }

        $application = $this->query->retrieveByPublicKey($publicKey);

        if (null === $application) {
            $this->handleEmptyApp($context);
            return;
        }

        // Set the application model to the manager.
        $this->manager->setApplication($application);

        // Set the appropriate redis cache prefix.
        $this->redisManager->appendApplicationPrefix($this->manager->getCompositeKey());

        // Remove the param from the query string.
        $request->query->remove($param);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request    = $event->getRequest();
        $attributes = $request->attributes;
        if (null === $attributes->get('_format')) {
            // Assume a JSON format if not set.
            $attributes->set('_format', 'json');
        }
        // Let the standard Symfony exception formatting take over...
        return;
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
            throw new HttpFriendlyException('No application was defined or found. Operations terminated.', 400);
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
}
