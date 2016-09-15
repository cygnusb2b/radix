<?php

namespace AppBundle\KernelSubscriber;

use AppBundle\Core\AccountManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AccountSubscriber implements EventSubscriberInterface
{
    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @param   AccountManager  $manager
     */
    public function __construct(AccountManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['checkApplication', 7], // 7 Ensures that this runs right after the firewall.
            ]
        ];
    }

    /**
     * Ensures that an application has been set.
     *
     * @param   GetResponseEvent    $event
     * @throws  \RuntimeException
     */
    public function checkApplication(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (false === $this->manager->hasApplication()) {
            throw new \RuntimeException('No application context has been selected. Unable to continue.');
        }
    }

}
