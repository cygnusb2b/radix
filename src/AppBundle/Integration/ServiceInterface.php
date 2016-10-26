<?php

namespace AppBundle\Integration;

interface ServiceInterface
{
    /**
     * Configures the service.
     * The parameters are generated from the service model via the database, and are read-only.
     *
     * @param   Parameters
     * @return  self
     */
    public function configure(array $parameters);

    /**
     * Gets the identity integration handler for this service.
     * If the service does not support this integration, a null value should be returned.
     *
     * @return  Handler\IdentifyInterface|null
     */
    public function getIdentifyHandler();

    /**
     * Executes a question pull.
     * Can contain additional, service specific arguments.
     * If supported, the service should return a question definition.
     * A value of anything other than QuestionDefinition will signify that this service does not support the integration.
     *
     * @param   string  $identifier
     * @param   array   $extra
     * @return  Definition\QuestionDefinition|mixed
     * @throws  \Exception On any internal service errors.
     */
    public function executeQuestionPull($externalId, array $extra = []);

    /**
     * Gets the service key.
     * Is ultimately used internally to retrieve the service model from the database.
     * Must be dasherized.
     *
     * @return  string
     */
    public function getKey();

    /**
     * Determines if the service is configured properly.
     *
     * @return  bool
     */
    public function hasValidConfig();
}
