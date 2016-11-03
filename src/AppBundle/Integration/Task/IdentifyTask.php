<?php

namespace AppBundle\Integration\Task;

use AppBundle\Integration\Execution\IdentifyExecution;
use As3\Bundle\PostProcessBundle\Task\TaskInterface;
use As3\Modlr\Models\Model;

class IdentifyTask implements TaskInterface
{
    /**
     * @var Model
     */
    protected $identity;

    /**
     * @var IdentifyExecution
     */
    protected $execution;

    /**
     * @param   Model               $identity
     * @param   IdentifyExecution   $execution
     * @throws  \InvalidArgumentException
     */
    public function __construct(Model $identity, IdentifyExecution $execution)
    {
        if ('identity-external' !== $identity->getType()) {
            throw new \InvalidArgumentException(sprintf('Expected a model type of `identity-external` but received `%s`', $identity->getType()));
        }
        $this->identity  = $identity;
        $this->execution = $execution;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->execution->run($this->identity);
    }
}
