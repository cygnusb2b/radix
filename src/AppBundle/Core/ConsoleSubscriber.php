<?php

namespace AppBundle\Core;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

class ConsoleSubscriber implements EventSubscriberInterface
{
    /**
     * @var AccountManager
     */
    private $manager;

    /**
     * @var RedisCacheManager
     */
    private $redisManager;

    /**
     * @var array
     */
    private $skip = [
        'as3:modlr:metadata:cache:clear' => true,
        'cache:clear'                    => true,
        'cache:warmup'                   => true,
    ];

    /**
     * @var ApplicationQuery
     */
    private $query;

    /**
     * @param   AccountManager      $manager
     * @param   ApplicationQuery    $query
     * @param   RedisCacheManager   $redisManager
     */
    public function __construct(AccountManager $manager, ApplicationQuery $query, RedisCacheManager $redisManager)
    {
        $this->manager = $manager;
        $this->query   = $query;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => [
                ['loadApplication'],
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
        $app  = getenv(AccountManager::ENV_KEY);
        $name = $event->getCommand()->getName();

        if (isset($this->skip[$name])) {
            return;
        }

        if (empty($app)) {
            $this->manager->allowDbOperations(false);
            return;
        }

        $application = $this->query->retrieveByAppKey($app);
        if (null === $application) {
            $this->manager->allowDbOperations(false);
            return;
        }

        $this->manager->setApplication($application);

        // Set the appropriate redis cache prefix.
        $this->redisManager->appendApplicationPrefix($this->manager->getCompositeKey());
    }
}
