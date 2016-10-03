<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Customer\CustomerManager;
use AppBundle\Customer\EmailVerifyTokenGenerator;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Factory\Customer\CustomerEmailFactory;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;

class CustomerEmailVerifySubmitHandler implements SubmissionHandlerInterface
{
    private $customerManager;

    private $emailModel;

    private $identityModel;

    private $tokenGenerator;

    public function __construct(EmailVerifyTokenGenerator $tokenGenerator, CustomerManager $customerManager)
    {
        $this->tokenGenerator   = $tokenGenerator;
        $this->customerManager  = $customerManager;
    }

    public function getStore()
    {
        return $this->customerManager->getStore();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {
        $this->identityModel = null;
        $verification = $this->emailModel->get('verification');
        $verification->set('verified', true);
        $verification->set('completedDate', new \DateTime());

        $account  = $this->emailModel->get('account');
        $identity = $this->customerManager->upsertIdentityFor($this->emailModel->get('value'));
        if (null !== $identity) {
            $identity->set('account', $account);
            $this->identityModel = $identity;
        }

        $submission->set('customer', $account);

        // Log the customer in.
        $this->customerManager->login($account);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        if (true !== $result = $this->customerManager->getAccountFactory()->getEmailFactory()->canSave($this->emailModel)) {
            $result->throwException();
        }
        if (null !== $this->identityModel) {
            if (true !== $result = $this->customerManager->getIdentityFactory()->canSave($this->identityModel)) {
                $result->throwException();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceKey()
    {
        return 'customer-email.verify-submit';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->emailModel->save();
        if (null !== $this->identityModel) {
            $this->customerManager->getIdentityFactory()->save($this->identityModel);
        }
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
        $model = $this->getStore()->findQuery('customer-email', ['verification.token' => $token])->getSingleResult();
        if (null === $model) {
            throw new HttpFriendlyException('No email address was found for the provided token.', 404);
        }
        if (true === $model->get('verification')->get('verified')) {
            throw new HttpFriendlyException(sprintf('The email address "%s" is already verified.', $model->get('value')), 400);
        }
        $this->tokenGenerator->parseFor($token, $model->get('value'), $model->get('account')->getId());

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
