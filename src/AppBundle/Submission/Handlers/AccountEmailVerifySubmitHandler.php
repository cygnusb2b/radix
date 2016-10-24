<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Identity\EmailVerifyTokenGenerator;
use AppBundle\Identity\IdentityManager;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccountEmailVerifySubmitHandler implements SubmissionHandlerInterface
{
    private $identityManager;

    private $emailModel;

    private $tokenGenerator;

    public function __construct(EmailVerifyTokenGenerator $tokenGenerator, IdentityManager $identityManager)
    {
        $this->tokenGenerator   = $tokenGenerator;
        $this->identityManager  = $identityManager;
    }

    public function getStore()
    {
        return $this->identityManager->getStore();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {
        $verification = $this->emailModel->get('verification');
        $verification->set('verified', true);
        $verification->set('completedDate', new \DateTime());

        $account = $this->emailModel->get('account');

        $submission->set('identity', $account);

        // Log the account in.
        $this->identityManager->login($account);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        if (true !== $result = $this->identityManager->getIdentityFactoryFor('identity-account')->getEmailFactory()->canSave($this->emailModel)) {
            $result->throwException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createResponseFor(Model $submission)
    {
        return new JsonResponse([
            'data' => []
        ], 201);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceKey()
    {
        return 'identity-account-email.verify-submit';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->emailModel->save();
    }

    /**
     * {@inheritdoc}
     */
    public function validateAlways(RequestPayload $payload)
    {
        // Reset previous email model.
        $this->emailModel = null;

        $token = $payload->getSubmission()->get('token');
        if (empty($token)) {
            throw new HttpFriendlyException('No email verification token was provided. Unable to verify.', 400);
        }
        $model = $this->getStore()->findQuery('identity-account-email', ['verification.token' => $token])->getSingleResult();
        if (null === $model) {
            throw new HttpFriendlyException('No email address was found for the provided token.', 404);
        }
        if (true === $model->get('verification')->get('verified')) {
            throw new HttpFriendlyException(sprintf('The email address "%s" is already verified.', $model->get('value')), 400);
        }
        $this->tokenGenerator->parseFor($token, $model->get('account')->getId(), ['emailAddress' => $model->get('value')]);

        // Set the email model for further processing.
        $this->emailModel = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedIn(RequestPayload $payload, Model $account)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedOut(RequestPayload $payload, Model $identity = null)
    {
    }
}
