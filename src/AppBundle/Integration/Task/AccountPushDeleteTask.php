<?php

namespace AppBundle\Integration\Task;

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
