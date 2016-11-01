<?php

namespace AppBundle\Integration\Task;

class AccountPushCreateTask extends AbstractAccountPushTask
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->execution->runCreate(
            $this->account
        );
    }
}
