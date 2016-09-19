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

    private $skip = [
        'as3:modlr:metadata:cache:clear' => true,
        'cache:clear'                    => true,
        'cache:warmup'                   => true,
    ];

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
        $app  = getenv('APP');
        $name = $event->getCommand()->getName();

        if (isset($this->skip[$name])) {
            return;
        }

        if (empty($app)) {
            $this->manager->allowDbOperations(false);
            return;
        }

        $application = $this->manager->retrieveByAppKey($app);
        if (null === $application) {
            $this->manager->allowDbOperations(false);
            return;
        }

        $this->manager->setApplication($application);
    }
}
