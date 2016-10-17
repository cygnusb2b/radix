<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Factory\Customer\CustomerAccountFactory;
use AppBundle\Customer\ResetPasswordTokenGenerator;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Security\User\CustomerProvider;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class CustomerAccountResetPasswordGenerateHandler implements SubmissionHandlerInterface
{
    /**
     * @var CustomerAccountFactory
     */
    private $customerFactory;

    /**
     * @var CustomerProvider
     */
    private $customerProvider;

    /**
     * @var Model|null
     */
    private $customerModel;

    /**
     * @var ResetPasswordTokenGenerator
     */
    private $tokenGenerator;

    /**
     * @param   CustomerAccountFactory      $customerFactory
     * @param   CustomerProvider            $customerProvider
     * @param   ResetPasswordTokenGenerator $tokenGenerator
     */
    public function __construct(CustomerAccountFactory $customerFactory, CustomerProvider $customerProvider, ResetPasswordTokenGenerator $tokenGenerator)
    {
        $this->customerFactory  = $customerFactory;
        $this->customerProvider = $customerProvider;
        $this->tokenGenerator   = $tokenGenerator;
    }

    /**
     * @return  \As3\Modlr\Store\Store
     */
    public function getStore()
    {
        return $this->customerProvider->getStore();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {
        $credentials = $this->customerModel->get('credentials');
        $password    = $credentials->get('password');
        if (null === $password) {
            // Customer is attemping to reset an account without a password crendetial (social, etc).
            // Create a temporary password before setting/sending the reset code.
            $password = $credentials->createEmbedFor('password');
            $password->set('value', uniqid());
            $credentials->set('password', $password);
        }

        $token = $this->tokenGenerator->createFor($this->customerModel->getId(), []);
        $password->set('resetCode', (string) $token);
        $credentials->set('password', $password);
        $this->customerModel->set('credentials', $credentials);

        // Force the submission to be linked to the account being reset.
        $submission->set('customer', $this->customerModel);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        if (true !== $result = $this->customerFactory->canSave($this->customerModel)) {
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
        return 'customer-account.reset-password-generate';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->customerModel->save();
    }

    /**
     * {@inheritdoc}
     */
    public function validateAlways(RequestPayload $payload)
    {
        // Reset previous customer model.
        $this->customerModel = null;

        $emailOrUsername = $payload->getCustomer()->get('primaryEmail');
        if (empty($emailOrUsername)) {
            throw new HttpFriendlyException('Unable to reset password: No email address or username was provided.', 400);
        }

        try {
            $this->customerModel = $this->customerProvider->findViaPasswordCredentials($emailOrUsername);
        } catch (AuthenticationException $e) {
            throw new HttpFriendlyException(sprintf('Unable to reset password: %s', $e->getMessage()), 400);
        }

        $credentials = $this->customerModel->get('credentials');
        if (null === $credentials) {
            throw new HttpFriendlyException('Unable to reset password: No existing credentials were found on this account.', 400);
        }
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
