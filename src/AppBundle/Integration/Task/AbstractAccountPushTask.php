<?php

namespace AppBundle\Integration\Task;

use AppBundle\Integration\Execution\AccountPushExecution;
use As3\Bundle\PostProcessBundle\Task\TaskInterface;
use As3\Modlr\Models\Model;

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
     * @throws  \InvalidArgumentException
     */
    public function __construct(Model $account, AccountPushExecution $execution)
    {
        if ('identity-account' !== $account->getType()) {
            throw new \InvalidArgumentException(sprintf('Expected a model type of `identity-account` but received `%s`', $account->getType()));
        }
        $this->account   = $account;
        $this->execution = $execution;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function run();
}
