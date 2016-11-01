<?php

namespace AppBundle\Integration\Handler;

use As3\Modlr\Models\Model;

interface AccountPushInterface extends HandlerInterface
{
    /**
     * Executes the account-push on account create.
     *
     * @todo    Should the handler be given the entire model, or a read-only representation?
     * @param   Model   $account
     * @throws  \Exception  On any internal push error.
     */
    public function onCreate(Model $account);

    /**
     * Executes the account-push on account delete.
     *
     * @todo    Should the handler be given the entire model, or a read-only representation?
     * @param   Model   $account
     * @throws  \Exception  On any internal push error.
     */
    public function onDelete(Model $account);

    /**
     * Executes the account-push on account update.
     *
     * @todo    Should the handler be given the entire model, or a read-only representation?
     * @param   Model   $account
     * @param   array   $changeset
     * @throws  \Exception  On any internal push error.
     */
    public function onUpdate(Model $account, array $changeset);
}
