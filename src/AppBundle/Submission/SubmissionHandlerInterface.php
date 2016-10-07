<?php

namespace AppBundle\Submission;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;

interface SubmissionHandlerInterface
{
    /**
     * Sent before the submission saving process begins.
     * Allows for additional logic to run against the submission, or for the handler to create more models, etc.
     *
     * @param   RequestPayload  $payload    The submission payload.
     */
    public function beforeSave(RequestPayload $payload, Model $submission);

    /**
     * Sent when the submission is being checked for savability.
     * Allows the handler to throw an exception if it deems that the submission (or other models) cannot be saved.
     * If an exception is thrown, the entire submission/customer save process will halt.
     *
     * @throws  HttpFriendlyException
     */
    public function canSave();

    /**
     * Creates the response for the submission.
     *
     * @param   Model   $submission
     * @return  \Symfony\Component\HttpFoundation\Response
     */
    public function createResponseFor(Model $submission);

    /**
     * Gets the source key this handler processes.
     *
     * @return  string
     */
    public function getSourceKey();

    /**
     * Sent when the submission is saved.
     * Allows the handler to save any additional models it may have created, handled.
     * Should also be used as a teardown method, when needed.
     */
    public function save();

    /**
     * Payload validation that always runs.
     *
     * @param   RequestPayload  $payload    The submission payload.
     * @throws  HttpFriendlyException       On payload validation failure.
     */
    public function validateAlways(RequestPayload $payload);

    /**
     * Payload validation that runs only if there's a logged in user.
     *
     * @param   RequestPayload  $payload    The submission payload.
     * @param   Model           $account    The logged in customer account.
     * @throws  HttpFriendlyException       On payload validation failure.
     */
    public function validateWhenLoggedIn(RequestPayload $payload, Model $account);

    /**
     * Payload validation that runs only if there isn't a logged in user.
     *
     * @param   RequestPayload  $payload    The submission payload.
     * @param   Model|null      $identity   The customer identity (if present).
     * @throws  HttpFriendlyException       On payload validation failure.
     */
    public function validateWhenLoggedOut(RequestPayload $payload, Model $identity = null);
}
