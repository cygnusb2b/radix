<?php

namespace AppBundle\Integrations;

use AppBundle\Integrations\Definitions\QuestionDefinition;
use As3\Parameters\DefinedParameters as Parameters;
use As3\Parameters\Definitions;

interface ClientInterface
{
    /**
     * Configures the client.
     *
     * @param   Parameters
     * @return  self
     */
    public function configure(array $parameters);

    /**
     * Creates a new parameter instance for the provided parameters.
     *
     * @param   array   $parameters
     * @return  Parameters
     */
    public function createParameterInstance(array $parameters);

    /**
     * Executes a question pull for the provided client identifier.
     * Can contain additional, client specific arguments.
     * If supported, the client should return a question definition.
     * A value of anything other than QuestionDefinition will signify that this client does not support the integration.
     *
     * @param   string  $identifier
     * @param   array   $args
     * @return  QuestionDefinition|mixed
     * @throws  \Exception On any internal client errors.
     */
    public function executeQuestionPull($identifier, array $args = []);

    /**
     * Gets the key that uniquely distinguishes this client.
     *
     * @return  string
     */
    public function getKey();

    /**
     * Gets the parameter definitions for this client.
     *
     * @return  Definitions
     */
    public function getParameterDefinitions();

    /**
     * Determines if this client supports the provided integration type.
     *
     * @param   string  $integrationType
     * @return  bool
     */
    public function hasSupportFor($integrationType);

    /**
     * Determines if the client is configured properly.
     *
     * @return  bool
     */
    public function hasValidConfig();


}
