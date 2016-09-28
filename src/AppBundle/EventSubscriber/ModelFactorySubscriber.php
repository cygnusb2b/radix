<?php

namespace AppBundle\EventSubscriber;

use As3\Modlr\Events\EventSubscriberInterface;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Events;
use As3\Modlr\Store\Events\ModelLifecycleArguments;
use As3\Modlr\Store\Store;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ModelFactorySubscriber implements EventSubscriberInterface
{
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
            Events::preCommit,
        ];
    }

    /**
     * @param   ModelLifecycleArguments     $args
     */
    public function preCommit(ModelLifecycleArguments $args)
    {
        $model   = $args->getModel();
        $factory = $this->getFactoryManager()->getFactoryFor($model);
        if (null === $factory) {
            return;
        }

        if (true !== $result = $factory->canSave($model)) {
            $result->throwException();
        }
        $factory->postValidate($model);
        $factory->postSave($model);
    }

    /**
     * @return  \AppBundle\Factory\SubscriberFactoryManager
     */
    private function getFactoryManager()
    {
        return $this->container->get('app_bundle.subscriber_factory_manager');
    }
}
