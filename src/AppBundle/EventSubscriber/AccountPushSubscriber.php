<?php

namespace AppBundle\EventSubscriber;

use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Subscriber than runs account-push integrations.
 *
 * @author Jacob Bare <jacob.bare@gmail.com>
 */
class AccountPushSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    public static $enabled = true;

    /**
     * @var array
     */
    private $changeSets = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param   ContainerInterface  $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getEvents()
    {
        return [
            Events::postCreate,
            Events::postDelete,
            Events::postUpdate,
            Events::preUpdate,
        ];
    }

    /**
     * Processes account models after create.
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function postCreate(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        $this->getManager()->accountPushCreate($model);
    }

    /**
     * Processes account models after delete.
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function postDelete(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        $this->getManager()->accountPushDelete($model);
    }

    /**
     * Processes account models after update.
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function postUpdate(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        $identifier = $model->getId();
        $changeSet  = (isset($this->changeSets[$identifier])) ? $this->changeSets[$identifier] : [];
        $this->getManager()->accountPushUpdate($model, $changeSet);
    }

    /**
     * Processes account models prior to update.
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function preUpdate(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false === $this->shouldProcess($model)) {
            return;
        }
        // Store changesets for later use.
        $this->changeSets[$model->getId()] = $model->getChangeSet();
    }

    /**
     * Determines if this subscriber should handle the model.
     *
     * @param   Model   $model
     * @return  bool
     */
    protected function shouldProcess(Model $model)
    {
        return 'identity-account' === $model->getType() && true == static::$enabled;
    }

    /**
     * @return  \AppBundle\Integration\IntegrationManager
     */
    private function getManager()
    {
        return $this->container->get('app_bundle.integration.manager');
    }
}
