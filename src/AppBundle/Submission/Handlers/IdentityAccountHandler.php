<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Factory\Identity\IdentityAccountFactory;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class IdentityAccountHandler implements SubmissionHandlerInterface
{
    /**
     * @var IdentityAccountFactory
     */
    private $accountFactory;

    /**
     * @var Model
     */
    private $newAccount;

    /**
     * @param   IdentityAccountFactory  $accountFactory
     */
    public function __construct(IdentityAccountFactory $accountFactory)
    {
        $this->accountFactory = $accountFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {
        // Reset any previous.
        $this->newAccount = null;

        // Create the new account and override the identity set by the manager.
        $this->newAccount = $this->accountFactory->create($payload->getIdentity()->all());
        $submission->set('identity', $this->newAccount);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        if (true !== $result = $this->accountFactory->canSave($this->newAccount)) {
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
        return 'identity-account';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $this->accountFactory->save($this->newAccount);
    }

    /**
     * {@inheritdoc}
     */
    public function validateAlways(RequestPayload $payload)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedIn(RequestPayload $payload, Model $account)
    {
        // Disallow creation while logged in.
        throw new HttpFriendlyException('An account is already logged in. Account creation is not available while logged in.', 400);
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedOut(RequestPayload $payload, Model $identity = null)
    {
    }
}
