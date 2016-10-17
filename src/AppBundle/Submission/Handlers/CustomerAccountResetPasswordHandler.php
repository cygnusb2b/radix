<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Customer\CustomerManager;
use AppBundle\Customer\ResetPasswordTokenGenerator;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Factory\Customer\CustomerEmailFactory;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomerAccountResetPasswordHandler implements SubmissionHandlerInterface
{
    private $customerManager;

    private $customerModel;

    private $tokenGenerator;

    public function __construct(ResetPasswordTokenGenerator $tokenGenerator, CustomerManager $customerManager)
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
        $password = $this->customerModel->get('credentials')->get('password');
        $password->set('resetCode', null);
        $password->set('value', $payload->getCustomer()->get('password'));

        $submission->set('customer', $this->customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        $credentials = $this->customerModel->get('credentials');
        if (true !== $result = $this->customerManager->getAccountFactory()->getCredentialsFactory()->canSave($credentials)) {
            $result->throwException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createResponseFor(Model $submission)
    {
        return new JsonResponse([
            'data' => [
                'customer'  => $submission->get('customer')->getId(),
                'email'     => $submission->get('customer')->get('primaryEmail'),
            ]
        ], 201);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceKey()
    {
        return 'customer-account.reset-password';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->customerModel->save();
        // Log the customer in.
        $this->customerManager->login($this->customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function validateAlways(RequestPayload $payload)
    {
        // Reset previous customer model.
        $this->customerModel = null;

        $token = $payload->getSubmission()->get('token');
        if (empty($token)) {
            throw new HttpFriendlyException('Unable to reset password: No password reset token was provided.', 400);
        }

        if ($payload->getCustomer()->get('password') !== $payload->getCustomer()->get('confirmPassword')) {
            throw new HttpFriendlyException('Unable to reset password: The password and confirm password do not match.', 400);
        }

        $criteria = ['credentials.password.resetCode' => $token];
        $account  = $this->getStore()->findQuery('customer-account', $criteria)->getSingleResult();
        if (null === $account) {
            throw new HttpFriendlyException('Unable to reset password: No account found for the provided token.', 400);
        }

        $this->tokenGenerator->parseFor($token, $account->getId(), []);

        // Set customer model for further processing.
        $this->customerModel = $account;
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedIn(RequestPayload $payload, Model $account)
    {
        // Disallow reset while logged in.
        throw new HttpFriendlyException('A customer account is already logged in. Reset password is not available while logged in - use change password instead.', 400);
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedOut(RequestPayload $payload, Model $identity = null)
    {
    }
}
