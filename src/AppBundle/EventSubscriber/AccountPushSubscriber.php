<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Utility\RequestUtility;
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
     * @var Model[]
     */
    private $processedAccounts = [];

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
        try {
            $this->getManager()->accountPushCreate($model);
        } catch (\Exception $e) {
            RequestUtility::notifyException($e);
        }

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
        try {
            $this->getManager()->accountPushDelete($model);
        } catch (\Exception $e) {
            RequestUtility::notifyException($e);
        }
    }

    /**
     * Processes account models after update.
     *
     * @param   ModelLifecycleArguments     $args
     */
    public function postUpdate(ModelLifecycleArguments $args)
    {
        $model = $args->getModel();
        if (false == static::$enabled) {
            return;
        }
        if (0 === stripos($model->getType(), 'identity-answer')) {
            $this->processAnswer($model);
            return;
        }
        if ('identity-account-email' === $model->getType()) {
            $this->processEmail($model);
            return;
        }

        if (false === $this->shouldProcess($model)) {
            return;
        }
        $identifier = $model->getId();
        $changeSet  = (isset($this->changeSets[$identifier])) ? $this->changeSets[$identifier] : [];
        try {
            $this->getManager()->accountPushUpdate($model, $changeSet);
            $this->processedAccounts[$identifier] = true;
        } catch (\Exception $e) {
            RequestUtility::notifyException($e);
        }
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

    /**
     * Processes an answer model change.
     *
     * @param   Model   $answer
     */
    private function processAnswer(Model $answer)
    {
        $identity = $answer->get('identity');
        if (null === $identity || 'identity-account' !== $identity->getType()) {
            return;
        }
        $identifier = $identity->getId();
        if (isset($this->processedAccounts[$identifier])) {
            return;
        }

        $this->getManager()->accountPushUpdate($identity, []);
        $this->processedAccounts[$identifier] = true;
    }

    /**
     * Processes an email model email change.
     *
     * @param   Model   $email
     */
    private function processEmail(Model $email)
    {
        $account = $email->get('account');
        if (null === $account) {
            return;
        }
        $identifier = $account->getId();
        if (isset($this->processedAccounts[$identifier])) {
            return;
        }

        $this->getManager()->accountPushUpdate($account, []);
        $this->processedAccounts[$identifier] = true;
    }
}
