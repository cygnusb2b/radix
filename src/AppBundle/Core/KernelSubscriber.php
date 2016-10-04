<?php

namespace AppBundle\Core;

use AppBundle\Cors\CorsDefinition;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Exception\HttpFriendlySerializer;
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
     * @var HttpFriendlySerializer
     */
    private $serializer;

    /**
     * @param   AccountManager          $manager
     * @param   ApplicationQuery        $query
     * @param   HttpUtils               $httpUtils
     * @param   HttpFriendlySerializer  $serializer
     * @param   RedisCacheManager       $cacheManager
     */
    public function __construct(AccountManager $manager, ApplicationQuery $query, HttpUtils $httpUtils, HttpFriendlySerializer $serializer, RedisCacheManager $redisManager)
    {
        $this->manager      = $manager;
        $this->query        = $query;
        $this->httpUtils    = $httpUtils;
        $this->redisManager = $redisManager;
        $this->serializer   = $serializer;
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
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if (!$event->isMasterRequest()) {
            return;
        }

        $exception  = $event->getException();
        $status     = $this->serializer->extractStatusCode($exception);
        $serialized = $this->serializer->queueToJson([$exception]);
        if (!is_string($serialized)) {
            throw $exception;
        }

        $response = $response ?: new Response();

        $response->setStatusCode($status);
        $response->setContent($serialized);
        $response->headers->set('content-type', 'application/json');

        $event->setResponse($response);

        return $response;
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
