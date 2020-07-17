<?php

namespace AppBundle\Integration\Handler;

use AppBundle\Integration\Definition\ExternalIdentityDefinition;

interface OptInPushInterface extends HandlerInterface
{
    /**
     * Executes the the optin-push integration.
     *
     * @param   string  $externalId     The third-party, external product/list identifier.
     * @param   string  $emailAddress   The email address to push.
     * @param   bool    $optedIn        Whether this email address as opted in or out of the provided product identifier
     * @param   array   $extra          Any extra arguments.
     * @throws  \Exception  On any internal pull error.
     */
    public function execute($externalId, $emailAddress, $optedIn, array $extra = []);
}
