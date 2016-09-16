<?php

namespace AppBundle\Core;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class ConsoleSubscriber implements EventSubscriberInterface
{
    const ENV_KEY = 'APP';

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
            ConsoleEvents::COMMAND => [
                ['loadApplication'], // 7 Ensures that this runs right after the firewall.
            ]
        ];
    }

    /**
     * Ensures that an application has been set.
     *
     * @param   ConsoleCommandEvent    $event
     * @throws  \RuntimeException
     */
    public function loadApplication(ConsoleCommandEvent $event)
    {
        $app = getenv('APP');

        if (empty($app)) {
            $event->getOutput()->writeLn(sprintf('<error>No application has been set. Set using APP="account:group"</error>'));
            $event->disableCommand();
            return;
        }

        $application = $this->manager->retrieveByAppKey($app);
        if (null === $application) {
            $event->getOutput()->writeLn(sprintf('<error>No application found for hthe provided APP context.</error>'));
            $event->disableCommand();
            return;
        }

        $this->manager->setApplication($application);
    }
}
