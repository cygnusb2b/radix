<?php

namespace AppBundle\Identity;

use AppBundle\Core\AccountManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class IdentitySubscriber implements EventSubscriberInterface
{
    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var IdentityManager
     */
    private $identityManager;

    /**
     * @param   AccountManager      $accountManager
     * @param   IdentityManager     $identityManager
     */
    public function __construct(AccountManager $accountManager, IdentityManager $identityManager)
    {
        $this->accountManager  = $accountManager;
        $this->identityManager = $identityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['handleCustomerCookies', -1]
            ],
        ];
    }

    /**
     * Handles setting/removing identity cookies based on the current identity state.
     *
     * @param   FilterResponseEvent     $event
     */
    public function handleCustomerCookies(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $context  = $this->accountManager->extractContextFrom($request);

        if ('application' !== $context) {
            return;
        }

        $path = $request->getPathInfo();
        if ('GET' === $request->getMethod() && 0 !== stripos($request->getPathInfo(), '/app/auth')) {
            return;
        }

        $response = $event->getResponse();

        $this->identityManager->setCookiesTo($response);
        if (true === $request->attributes->get('destroyCookies')) {
            $this->identityManager->destroyCookiesIn($response);
        }
        if (true === $request->attributes->get('destroySessionCookie')) {
            $this->identityManager->destroySessionCookie($response);
        }
    }
}
