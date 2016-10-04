<?php

namespace AppBundle\Customer;

use AppBundle\Core\AccountManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CustomerSubscriber implements EventSubscriberInterface
{
    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var CustomerManager
     */
    private $customerManager;

    /**
     * @param   AccountManager      $accountManager
     * @param   CustomerManager     $customerManager
     */
    public function __construct(AccountManager $accountManager, CustomerManager $customerManager)
    {
        $this->accountManager  = $accountManager;
        $this->customerManager = $customerManager;
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
     * Handles setting/removing customer cookies based on the current customer state.
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

        $this->customerManager->setCookiesTo($response);
        if (true === $request->attributes->get('destroyCookies')) {
            $this->customerManager->destroyCookiesIn($response);
        }
        if (true === $request->attributes->get('destroySessionCookie')) {
            $this->customerManager->destroySessionCookie($response);
        }
    }
}
