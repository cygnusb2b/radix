<?php

namespace AppBundle\Integration\Task;

use AppBundle\Integration\Execution\OptInPushExecution;
use As3\Bundle\PostProcessBundle\Task\TaskInterface;
use As3\Modlr\Models\Model;

class OptInPushTask implements TaskInterface
{
    /**
     * @var string
     */
    protected $emailAddress;

    /**
     * @var OptInPushExecution
     */
    protected $execution;

    /**
     * @var bool
     */
    protected $optedIn;

    /**
     * @param   string              $emailAddress
     * @param   bool                $optedIn
     * @param   OptInPushExecution  $execution
     */
    public function __construct($emailAddress, $optedIn, OptInPushExecution $execution)
    {
        $this->emailAddress  = $emailAddress;
        $this->optedIn  = $optedIn;
        $this->execution = $execution;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->execution->run($this->emailAddress, $this->optedIn);
    }
}
