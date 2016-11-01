<?php

namespace AppBundle\Integration\Task;

use AppBundle\Integration\Execution;
use As3\Modlr\Models\Model;
use As3\PostProcessBundle\Task\TaskInterface;

abstract class AbstractAccountPushTask implements TaskInterface
{
    /**
     * @var Model
     */
    protected $account;

    /**
     * @var AccountPushExecution
     */
    protected $execution;

    /**
     * @param   Model                   $account
     * @param   AccountPushExecution    $execution
     */
    public function __construct(Model $account, AccountPushExecution $execution)
    {
        $this->account   = $account;
        $this->execution = $execution;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function run();
}
