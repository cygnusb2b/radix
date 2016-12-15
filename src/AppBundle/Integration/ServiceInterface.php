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
     * Gets the account-push integration handler for this service.
     * If the service does not support this integration, a null value should be returned.
     *
     * @return  Handler\AccountPushInterface|null
     */
    public function getAccountPushHandler();

    /**
     * Gets the identity integration handler for this service.
     * If the service does not support this integration, a null value should be returned.
     *
     * @return  Handler\IdentifyInterface|null
     */
    public function getIdentifyHandler();

    /**
     * Gets the service key.
     * Is ultimately used internally to retrieve the service model from the database.
     * Must be dasherized.
     *
     * @return  string
     */
    public function getKey();

    /**
     * Gets the optin-push integration handler for this service.
     * If the service does not support this integration, a null value should be returned.
     *
     * @return  Handler\OptInPushInterface|null
     */
    public function getOptInPushHandler();

    /**
     * Gets the question-pull integration handler for this service.
     * If the service does not support this integration, a null value should be returned.
     *
     * @return  Handler\QuestionPullInterface|null
     */
    public function getQuestionPullHandler();

    /**
     * Determines if the service is configured properly.
     *
     * @return  bool
     */
    public function hasValidConfig();
}
