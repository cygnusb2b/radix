<?php

namespace AppBundle\Integration\Task;

use AppBundle\Integration\Execution\AccountPushExecution;
use As3\Modlr\Models\Model;

class AccountPushUpdateTask extends AbstractAccountPushTask
{
    /**
     * @var array
     */
    private $changeSet;

    /**
     * @param   Model                   $account
     * @param   AccountPushExecution    $execution
     * @param   array                   $changeSet
     */
    public function __construct(Model $account, AccountPushExecution $execution, array $changeSet)
    {
        parent::__construct($account, $execution);
        $this->changeSet = $changeSet;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->execution->runUpdate(
            $this->account,
            $this->changeSet
        );
    }
}
