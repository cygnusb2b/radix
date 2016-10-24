<?php

namespace AppBundle\Submission\Handlers;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Factory\Identity\AccountEmailFactory;
use AppBundle\Submission\SubmissionHandlerInterface;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccountEmailVerifyGenerateHandler implements SubmissionHandlerInterface
{
    /**
     * @var AccountEmailFactory
     */
    private $emailFactory;

    /**
     * @var Model|null
     */
    private $emailModel;

    /**
     * @param   AccountEmailFactory     $emailFactory
     */
    public function __construct(AccountEmailFactory $emailFactory)
    {
        $this->emailFactory = $emailFactory;
    }

    /**
     * @return  \As3\Modlr\Store\Store
     */
    public function getStore()
    {
        return $this->emailFactory->getStore();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave(RequestPayload $payload, Model $submission)
    {
        $model = $this->emailModel;

        $verification = $model->get('verification');
        $verification->set('token', null); // Unset the token so the underlying subscriber will re-generate.
        $verification->set('completedDate', null);

        // Force the submission to be linked to the account being verified.
        $submission->set('identity', $model->get('account'));
    }

    /**
     * {@inheritdoc}
     */
    public function canSave()
    {
        if (true !== $result = $this->emailFactory->canSave($this->emailModel)) {
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
        return 'identity-account-email.verify-generate';
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

        $emailAddress = $payload->getIdentity()->get('primaryEmail');
        $accountId    = $payload->getIdentity()->get('id');

        $emailAddress = ModelUtility::formatEmailAddress($emailAddress);
        if (empty($emailAddress)) {
            throw new HttpFriendlyException('No email address was provided. Unable to send verification email.', 400);
        }
        $customer = $this->emailFactory->retrieveCustomerViaEmailAddress($emailAddress);
        if (null !== $customer) {
            throw new HttpFriendlyException(sprintf('The email address "%s" is already verified.', $emailAddress), 400);
        }

        $model = $this->retrieveUnverifiedEmailModelFor($emailAddress, $accountId);
        if (null === $model) {
            throw new HttpFriendlyException(sprintf('No linked account was found for email address "%s"', $emailAddress), 404);
        }

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

    /**
     * Retrieve an unverified, customer email model for the provided email and customer id.
     * Customer id is optional, but highly recommended, and should be used in all cases.
     * Fallback logic exists if a customer id is not provided where, if multiple addresses are found, it will use the most recently created.
     *
     * @param   string  $emailAddress
     * @param   string  $accountId
     * @return  Model|null
     */
    private function retrieveUnverifiedEmailModelFor($emailAddress, $accountId)
    {
        $criteria = [
            'value'                 => $emailAddress,
            'verification.verified' => false,
        ];
        if (HelperUtility::isMongoIdFormat($accountId)) {
            $criteria['account'] = $accountId;
        }
        return $this->getStore()->findQuery('identity-account-email', $criteria, [], ['createdDate' => -1])->getSingleResult();
    }
}
