<?php

namespace AppBundle\Integration\Handler;

use AppBundle\Integration\Definition\ExternalIdentityDefinition;

interface QuestionPullInterface extends HandlerInterface
{
    /**
     * Executes the the identify integration.
     *
     * @param   string  $externalId  The third-party, external identifier.
     * @param   array   $extra      Any extra arguments.
     * @return  QuestionDefinition
     * @throws  \Exception  On any internal pull error.
     */
    public function execute($externalId, array $extra = []);
}
