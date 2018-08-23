<?php

namespace AppBundle\Integration\Execution;

use AppBundle\Integration\Handler\HandlerInterface;
use AppBundle\Integration\Handler\OptInPushInterface;
use AppBundle\Utility\ModelUtility;
use As3\Modlr\Models\Model;

class OptInPushExecution extends AbstractExecution
{
    /**
     * Executes the opt-in push integration.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @param   string  $emailAddress
     * @param   bool    $optedIn
     */
    public function run($emailAddress, $optedIn)
    {
        $emailAddress = ModelUtility::formatEmailAddress($emailAddress);
        $integration  = $this->getIntegration();
        $externalId   = $integration->get('identifier');
        $handler      = $this->getHandler();

        $handler->execute($externalId, $emailAddress, (bool) $optedIn, (array) $integration->get('extra'));

        $this->updateIntegrationDetails();
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedModelType()
    {
        return 'integration-optin-push';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateImplements(HandlerInterface $handler)
    {
        if (!$handler instanceof OptInPushInterface) {
            throw new \InvalidArgumentException('The handler is unsupported. Expected an implementation of OptInPushInterface');
        }
    }
}
