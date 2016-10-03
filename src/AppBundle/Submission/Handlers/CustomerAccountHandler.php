<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Factory\Customer\CustomerAccountFactory as AccountFactory;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;

class CustomerAccountHandler implements SubmissionHandlerInterface
{
    /**
     * @var AccountFactory
     */
    private $accountFactory;

    /**
     * @var Model
     */
    private $newAccount;

    /**
     * @param   AccountFactory  $accountFactory
     */
    public function __construct(AccountFactory $accountFactory)
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
        $this->newAccount = $this->accountFactory->create($payload->getCustomer()->all());
        $submission->set('customer', $this->newAccount);
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
    public function getSourceKey()
    {
        return 'customer-account';
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
        throw new HttpFriendlyException('A customer account is already logged in. Account creation is not available while logged in.', 400);
    }

    /**
     * {@inheritdoc}
     */
    public function validateWhenLoggedOut(RequestPayload $payload, Model $identity = null)
    {
    }
}
