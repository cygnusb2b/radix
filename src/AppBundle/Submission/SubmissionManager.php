<?php

namespace AppBundle\Submission;

use AppBundle\Customer\CustomerManager;
use AppBundle\Factory\InputSubmissionFactory;
use AppBundle\Notifications\NotificationManager;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;

class SubmissionManager
{
    /**
     * @var CustomerManager
     */
    private $customerManager;

    /**
     * @var SubmissionHandlerInterface
     */
    private $handlers = [];

    /**
     * @var NotificationManager
     */
    private $notificationManager;

    /**
     * @var InputSubmissionFactory
     */
    private $submissionFactory;

    /**
     * @param   InputSubmissionFactory  $submissionFactory
     * @param   CustomerManager         $customerManager
     * @param   NotificationManager     $notificationManager
     */
    public function __construct(InputSubmissionFactory $submissionFactory, CustomerManager $customerManager, NotificationManager $notificationManager)
    {
        $this->submissionFactory   = $submissionFactory;
        $this->customerManager     = $customerManager;
        $this->notificationManager = $notificationManager;
    }

    /**
     * @param   SubmissionHandlerInterface    $handler
     * @return  self
     */
    public function addHandler(SubmissionHandlerInterface $handler)
    {
        $this->handlers[$handler->getSourceKey()] = $handler;
        return $this;
    }

    /**
     * Handles a submission for the provided source key and payload.
     *
     * @param   string          $sourceKey
     * @param   RequestPayload  $payload
     */
    public function processFor($sourceKey, RequestPayload $payload)
    {
        // Send the validate always hook.
        $this->callHookFor($sourceKey, 'validateAlways', [$payload]);

        // Send the appropriate customer state validation hook.
        $activeCustomer = $this->customerManager->getActiveCustomer();
        if (null !== $activeCustomer && 'customer-account' === $activeCustomer->getType()) {
            $this->callHookFor($sourceKey, 'validateWhenLoggedIn', [$payload, $activeCustomer]);
        } else {
            $this->callHookFor($sourceKey, 'validateWhenLoggedOut', [$payload, $activeCustomer]);
        }

        // Create the submission.
        $submission = $this->createSubmission($sourceKey, $payload);

        // Do the customer/submission "dance."
        $customer = $this->determineCustomer($submission, $payload);
        if (null !== $customer) {
            $customerFactory = $this->customerManager->getCustomerFactoryFor($customer);
            $submission->set('customer', $customer);
        }

        // Send the before save hook to allow the handler to perform additional logic.
        $this->callHookFor($sourceKey, 'beforeSave', [$payload, $submission]);

        // Throw error if unable to save the customer or the submission.
        if (null !== $customer && true !== $result = $customerFactory->canSave($customer)) {
            $result->throwException();
        }
        if (true !== $result = $this->submissionFactory->canSave($submission)) {
            $result->throwException();
        }

        // Send the can save hook to allow for additional save checks.
        $this->callHookFor($sourceKey, 'canSave', []);

        // Save the customer and submission
        if (null !== $customer) {
            $customerFactory->save($customer);
        }
        $this->submissionFactory->save($submission);

        // Send the save hook for additional saving.
        $this->callHookFor($sourceKey, 'save', []);

        // Set the active identity, if applicable.
        if (null !== $customer && 'customer-identity' === $customer->getType()) {
            $this->customerManager->setActiveIdentity($customer);
        }

        // Send email notifications.
        $this->notificationManager->sendNotificationFor($submission);

        // Return the response.
        return $this->callHookFor($sourceKey, 'createResponseFor', [$submission]);
    }

    /**
     * Calls a handler hook method.
     *
     * @param   string  $sourceKey
     * @param   string  $method
     * @param   array   $args
     */
    private function callHookFor($sourceKey, $method, array $args)
    {
        if (isset($this->handlers[$sourceKey])) {
            $handler = $this->handlers[$sourceKey];
            return call_user_func_array([$handler, $method], $args);
        }
    }

    /**
     * Creates a submission model for the provided source key.
     *
     * @param   string          $sourceKey
     * @param   RequestPayload  $payload
     * @return  Model
     */
    private function createSubmission($sourceKey, RequestPayload $payload)
    {
        $submission = $this->submissionFactory->create($payload);
        $submission->set('sourceKey', $sourceKey);
        return $submission;
    }

    /**
     * Determines the customer to use for the submission.
     * Will use an identity if an account is not logged in.
     * If no identity is found, it will create one.
     *
     * @todo    Will need to determine how to get the identity if an email isn't provided with the submission.
     * @param   Model           $submission
     * @param   RequestPayload  $payload
     * @return  Model|null      The appropriate customer for the submission.
     */
    private function determineCustomer(Model $submission, RequestPayload $payload)
    {
        if (null !== $customer = $this->customerManager->getActiveAccount()) {
            // Logged in customer.
            // Make sure email isn't updated by this form. @todo Will need to determine a better way of handling this.
            $payload->getCustomer()->remove('primaryEmail');
            $payload->getCustomer()->remove('emails');
            // Update customer data with the submission data.
            $this->customerManager->getAccountFactory()->apply($customer, $payload->getCustomer()->all());
            return $customer;
        }
        // Customer is not logged in. Create/update the identity, if possible.
        $emailAddress = $payload->getCustomer()->get('primaryEmail');
        return $this->customerManager->upsertIdentityFor($emailAddress, $payload->getCustomer()->all());
    }
}
