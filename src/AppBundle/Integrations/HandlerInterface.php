<?php

namespace AppBundle\Integrations;

use AppBundle\Integrations\Definitions\QuestionDefinition;
use As3\Parameters\DefinedParameters as Parameters;
use As3\Parameters\Definitions;

interface HandlerInterface
{
    /**
     * Gets the unique that identifies this integration handler.
     *
     * @return  string
     */
    public function getKey();

    /**
     * Runs the integration for all matching identifiers.
     *
     */
    public function run();

    /**
     * Runs the integration for a specific identifier.
     *
     * @param   string  $identifier
     */
    public function runFor($identifier);

    /**
     * Sets the integration manager service to the handler.
     *
     * @param   IntegrationManager  $manager
     */
    public function setManager(IntegrationManager $manager);
}
