<?php

namespace AppBundle\Integration\Execution;

use AppBundle\Integration\Handler\HandlerInterface;
use AppBundle\Integration\Handler\IdentifyInterface;
use As3\Modlr\Models\Model;

class IdentifyExecution extends AbstractExecution
{
    /**
     * Executes the identify integration.
     *
     * @param   string  $externalId
     * @return  Model
     */
    public function run($externalId)
    {
        $handler = $this->getHandler();
        list($source, $identifier) = $handler->getSourceAndIdentifierFor($externalId);

        $identity = $this->getStore()->findQuery('identity-external', ['source' => $source, 'identifier' => $identifier])->getSingleResult();
        if (null === $identity) {
            // Immediately create. Will update the model data later.
            $identity = $this->store->create('identity-external');
            $identity->set('source', $source);
            $identity->set('identifier', $identifier);
            $identity->save();
        }

        // @todo At this point, the actual identification and updating of the identity model should be handled post-process.
        $definition = $handler->execute($identifier);

        // @todo Determine if a question pull integration also exists with this service id. If so, sync question answers.

        // @todo Must clear the existing model (if not new) and apply the definition to the model.

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateImplements(HandlerInterface $handler)
    {
        if (!$handler instanceof IdentifyInterface) {
            throw new \InvalidArgumentException('The handler is unsupported. Expected an implementation of IdentifyInterface');
        }
    }
}
