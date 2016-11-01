<?php

namespace AppBundle\Integration\Task;

use AppBundle\Integration\Execution;
use As3\Modlr\Models\Model;
use As3\PostProcessBundle\Task\TaskInterface;

class AccountPushDeleteTask extends AbstractAccountPushTask
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->execution->runDelete(
            $this->account
        );
    }
}
