<?php

namespace AppBundle\Integration\Execution;

use AppBundle\Integration\Handler\HandlerInterface;
use AppBundle\Integration\Handler\AccountPushInterface;
use As3\Modlr\Models\Model;

class AccountPushExecution extends AbstractExecution
{
    /**
     * Executes the account-push integration on create.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @param   Model   $account
     */
    public function runCreate(Model $account)
    {
        $this->validateAccountModel($account);
        var_dump(__METHOD__);
        die();
    }

    /**
     * Executes the account-push integration on delete.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @param   Model   $account
     */
    public function runDelete(Model $account)
    {
        $this->validateAccountModel($account);
        var_dump(__METHOD__);
        die();
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
        $this->validateAccountModel($account);
        $this->getHandler()->onUpdate($account, $changeSet);

        // Add/update the integration details here.

        var_dump(__METHOD__);
        die();
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
     * Ensures that the provided model is an `identity-account.`
     *
     * @param   Model   $account
     * @throws  \InvalidArgumentException
     */
    private function validateAccountModel(Model $account)
    {
        if ('identity-account' !== $account->getType()) {
            throw new \InvalidArgumentException(sprintf('The provided model type of `%s` is not supported. Expected type of `identity-account`', $account->getType()));
        }
    }
}
