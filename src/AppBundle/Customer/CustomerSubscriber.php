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
        if (null !== $customer = $this->manager->getActiveCustomer()) {
            $cookies = $this->manager->createCookiesFor($customer);
            foreach ($cookies as $instance) {
                $event->getResponse()->headers->setCookie($instance->toCookie());;
            }
        }
        if (true === $event->getRequest()->attributes->get('destroyCookies')) {
            foreach ($this->manager->getCookieNames() as $name) {
                $event->getResponse()->headers->clearCookie($name, AccountManager::APP_PATH);
            }
        }
    }
}
