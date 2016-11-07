<?php

namespace AppBundle\Integration\Handler;

use As3\Modlr\Models\Model;

interface AccountPushInterface extends HandlerInterface
{
    /**
     * Executes the account-push on account create.
     *
     * @param   Model   $account
     * @param   Model[] $questions    The third-party, external questions push answers for.
     * @return  string  The external identifier of the created external account record.
     * @throws  \Exception  On any internal push error.
     */
    public function onCreate(Model $account, array $questions);

    /**
     * Executes the account-push on account delete.
     *
     * @param   Model   $account
     * @return  string  The external identifier of the deleted external account record.
     * @throws  \Exception  On any internal push error.
     */
    public function onDelete(Model $account);

    /**
     * Executes the account-push on account update.
     *
     * @param   Model           $account
     * @param   string|null     $externalId The previously set external id when this data was last pushed via this handler.
     * @param   array           $changeSet  The account model change set.
     * @param   Model[]         $questions  The third-party, external questions to push answers for.
     * @return  string  The external identifier of the updated external account record.
     * @throws  \Exception  On any internal push error.
     */
    public function onUpdate(Model $account, $externalId, array $changeSet, array $questions);
}
