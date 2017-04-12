<?php

namespace AppBundle\Submission;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;

interface IdentifiableSubmissionHandlerInterface extends SubmissionHandlerInterface
{
    /**
     * Allows the handler to specifically override how the identity is handled.
     * Returning null will result in the default identity creation process.
     * Returning an Identity model will set the provided identity model to the submission.
     *
     * @param   RequestPayload  $payload
     * @return  Model|null
     */
    public function createIdentityFor(RequestPayload $payload);
}
