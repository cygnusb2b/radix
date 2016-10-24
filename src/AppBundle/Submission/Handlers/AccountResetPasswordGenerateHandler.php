<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Factory\Identity\IdentityAccountFactory;
use AppBundle\Identity\ResetPasswordTokenGenerator;
use AppBundle\Security\User\AccountProvider;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountResetPasswordGenerateHandler implements SubmissionHandlerInterface
{
    /**
     * @var IdentityAccountFactory
     */
    private $accountFactory;

    /**
     * @var Model|null
     */
    private $accountModel;

    /**
     * @var AccountProvider
     */
    private $accountProvider;

    /**
     * @var ResetPasswordTokenGenerator
     */
    private $tokenGenerator;

    /**
     * @param   IdentityAccountFactory      $accountFactory
     * @param   AccountProvider             $accountProvider
     * @param   ResetPasswordTokenGenerator $tokenGenerator
     */
    public function __construct(IdentityAccountFactory $accountFactory, AccountProvider $accountProvider, ResetPasswordTokenGenerator $tokenGenerator)
    {
        $this->accountFactory  = $accountFactory;
        $this->accountProvider = $accountProvider;
        $this->tokenGenerator  = $tokenGenerator;
    }

    /**
     * @return  \As3\Modlr\Store\Store
     */
    public function getStore()
    {
        return $this->accountProvider->getStore();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {
        $credentials = $this->accountModel->get('credentials');
        $password    = $credentials->get('password');
        if (null === $password) {
            // Customer is attemping to reset an account without a password crendetial (social, etc).
            // Create a temporary password before setting/sending the reset code.
            $password = $credentials->createEmbedFor('password');
            $password->set('value', uniqid());
            $credentials->set('password', $password);
        }

        $token = $this->tokenGenerator->createFor($this->accountModel->getId(), []);
        $password->set('resetCode', (string) $token);
        $credentials->set('password', $password);
        $this->accountModel->set('credentials', $credentials);

        // Force the submission to be linked to the account being reset.
        $submission->set('identity', $this->accountModel);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        if (true !== $result = $this->accountFactory->canSave($this->accountModel)) {
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
        return 'identity-account.reset-password-generate';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->accountModel->save();
    }

    /**
     * {@inheritdoc}
     */
    public function validateAlways(RequestPayload $payload)
    {
        // Reset previous account model.
        $this->accountModel = null;

        $emailOrUsername = $payload->getIdentity()->get('primaryEmail');
        if (empty($emailOrUsername)) {
            throw new HttpFriendlyException('Unable to reset password: No email address or username was provided.', 400);
        }

        try {
            $this->accountModel = $this->accountProvider->findViaPasswordCredentials($emailOrUsername);
        } catch (AuthenticationException $e) {
            throw new HttpFriendlyException(sprintf('Unable to reset password: %s', $e->getMessage()), 400);
        }

        $credentials = $this->accountModel->get('credentials');
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
        throw new HttpFriendlyException('An account is already logged in. Reset password is not available while logged in - use change password instead.', 400);
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedOut(RequestPayload $payload, Model $identity = null)
    {
    }
}
