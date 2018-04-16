<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Identity\IdentityManager;
use AppBundle\Identity\ResetPasswordTokenGenerator;
use AppBundle\Submission\IdentifiableSubmissionHandlerInterface;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccountResetPasswordHandler implements IdentifiableSubmissionHandlerInterface
{
    private $identityManager;

    private $accountModel;

    private $tokenGenerator;

    public function __construct(ResetPasswordTokenGenerator $tokenGenerator, IdentityManager $identityManager)
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
    public function createIdentityFor(RequestPayload $payload)
    {
        if (!empty($this->accountModel)) {
            return $this->accountModel;
        }

        $token = $payload->getSubmission()->get('token');
        if (empty($token)) {
            throw new HttpFriendlyException('Unable to reset password: No password reset token was provided.', 400);
        }
        $criteria = ['credentials.password.resetCode' => $token];
        $identity  = $this->getStore()->findQuery('identity-account', $criteria)->getSingleResult();

        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {
        $password = $this->accountModel->get('credentials')->get('password');
        $password->set('mechanism', 'platform');
        $password->set('resetCode', null);
        $password->set('value', $payload->getIdentity()->get('password'));
        $password->set('salt', null);

        $submission->set('identity', $this->accountModel);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        $credentials = $this->accountModel->get('credentials');
        if (true !== $result = $this->identityManager->getIdentityFactoryFor('identity-account')->getCredentialsFactory()->canSave($credentials)) {
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
                'account'   => $submission->get('identity')->getId(),
                'email'     => $submission->get('identity')->get('primaryEmail'),
            ]
        ], 201);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceKey()
    {
        return 'identity-account.reset-password';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->accountModel->save();
        // Log the customer in.
        $this->identityManager->login($this->accountModel);
    }

    /**
     * {@inheritdoc}
     */
    public function validateAlways(RequestPayload $payload)
    {
        // Reset previous customer model.
        $this->accountModel = null;

        $token = $payload->getSubmission()->get('token');
        if (empty($token)) {
            throw new HttpFriendlyException('Unable to reset password: No password reset token was provided.', 400);
        }

        if ($payload->getIdentity()->get('password') !== $payload->getIdentity()->get('confirmPassword')) {
            throw new HttpFriendlyException('Unable to reset password: The password and confirm password do not match.', 400);
        }

        $criteria = ['credentials.password.resetCode' => $token];
        $account  = $this->getStore()->findQuery('identity-account', $criteria)->getSingleResult();
        if (null === $account) {
            throw new HttpFriendlyException('Unable to reset password: No account found for the provided token.', 400);
        }

        $this->tokenGenerator->parseFor($token, $account->getId(), []);

        // Set account model for further processing.
        $this->accountModel = $account;
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
