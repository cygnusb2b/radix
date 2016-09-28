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
     * @var CustomerManager
     */
    private $manager;

    /**
     * @param   CustomerManager     $manager
     */
    public function __construct(CustomerManager $manager)
    {
        $this->manager = $manager;
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
        $response = $event->getResponse();

        $this->manager->setCookiesTo($response);
        if (true === $request->attributes->get('destroyCookies')) {
            $this->manager->destroyCookiesIn($response);
        }
        if (true === $request->attributes->get('destroySessionCookie')) {
            $this->manager->destroySessionCookie($response);
        }
    }
}
