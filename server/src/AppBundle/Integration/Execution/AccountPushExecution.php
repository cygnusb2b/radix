<?php

namespace AppBundle\Integration\Execution;

use AppBundle\EventSubscriber\AccountPushSubscriber;
use AppBundle\Integration\Handler\HandlerInterface;
use AppBundle\Integration\Handler\AccountPushInterface;
use As3\Modlr\Models\Embed;
use As3\Modlr\Models\Model;

class AccountPushExecution extends AbstractExecution
{
    /**
     * Get valid identity model types that can push.
     *
     * @return  string[]
     */
    public static function getValidModelTypes()
    {
        return ['identity-account' => true, 'identity-internal' => true];
    }

    /**
     * Determines if the provided model type can be pushed.
     *
     * @param   string  $type
     * @return  bool
     */
    public static function isModelTypeValid($type)
    {
        $types = self::getValidModelTypes();
        return isset($types[$type]);
    }

    /**
     * @param   string
     * @throws  \InvalidArgumentException
     */
    public static function validateModelType($type)
    {
        if (false === self::isModelTypeValid($type)) {
            throw new \InvalidArgumentException(sprintf(
                'The provided model type of `%s` is not supported. Expected type(s) of `%s`',
                $type,
                implode('`, `', array_keys(self::getValidModelTypes()))
            ));
        }
    }

    /**
     * Executes the account-push integration on create.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @param   Model   $account
     */
    public function runCreate(Model $account)
    {
        $this->validateIdentityModel($account);
        $identifier = $this->getHandler()->onCreate(
            $account,
            $this->retrieveExternalQuestions()
        );
        $this->updatePushDetailsFor($account, $identifier);
        $this->updateIntegrationDetails();
    }

    /**
     * Executes the account-push integration on delete.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @param   Model   $account
     */
    public function runDelete(Model $account)
    {
        $this->validateIdentityModel($account);
        $this->getHandler()->onDelete($account);
    }

    /**
     * Executes the account-push integration on update.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @param   Model   $account
     * @param   array   $changeSet
     */
    public function runUpdate(Model $account, array $changeSet)
    {
        $this->validateIdentityModel($account);
        $identifier = $this->getHandler()->onUpdate(
            $account,
            $this->extractExternalIdFor($account),
            $changeSet,
            $this->retrieveExternalQuestions()
        );
        $this->updatePushDetailsFor($account, $identifier);
        $this->updateIntegrationDetails();
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedModelType()
    {
        return 'integration-account-push';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateImplements(HandlerInterface $handler)
    {
        if (!$handler instanceof AccountPushInterface) {
            throw new \InvalidArgumentException('The handler is unsupported. Expected an implementation of AccountPushInterface');
        }
    }

    /**
     * Extracts the push integration details that were set for this account push.
     *
     * @param   Model   $account
     * @return  Embed|null
     */
    private function extractPushDetailsFor(Model $account)
    {
        if (null === $integration = $account->get('integration')) {
            // No integration details set.
            return;
        }
        foreach ($integration->get('push') as $push) {
            if ($push->get('integrationId') === $this->getIntegration()->getId()) {
                return $push;
            }
        }
    }

    /**
     * Extracts the external identifier that was set from this account push.
     *
     * @param   Model   $account
     * @return  string|null
     */
    private function extractExternalIdFor(Model $account)
    {
        $push = $this->extractPushDetailsFor($account);
        if (null === $push) {
            return;
        }
        $identifier = $push->get('identifier');
        return empty($identifier) ? null : $identifier;
    }

    /**
     * Updates the push integration details for the provided account.
     *
     * @param   Model   $account
     * @param   string  $externalId
     */
    private function updatePushDetailsFor(Model $account, $externalId)
    {
        $push = $this->extractPushDetailsFor($account);
        if (null === $push) {
            if (null === $integration = $account->get('integration')) {
                $account->set('integration', $account->createEmbedFor('integration'));
            }
            $push = $account->get('integration')->createEmbedFor('push');
            $account->get('integration')->pushEmbed('push', $push);
        }

        $now   = new \DateTime();
        $times = $push->get('timesRan');
        $push->set('identifier', $externalId);
        if (null === $push->get('firstRunDate')) {
            $push->set('firstRunDate', $now);
        }
        $push->set('lastRunDate', $now);
        $push->set('timesRan', ++$times);
        $push->set('integrationId', $this->getIntegration()->getId());

        $account->set('touchedDate', new \MongoDate());

        // Must disable the push subscriber so this save doesn't re-trigger another push!
        AccountPushSubscriber::$enabled = false;
        $account->save();
        AccountPushSubscriber::$enabled = true;
    }

    /**
     * Ensures that the provided model is supported.
     *
     * @param   Model   $account
     * @throws  \InvalidArgumentException
     */
    private function validateIdentityModel(Model $account)
    {
        self::validateModelType($account->getType());
    }
}
