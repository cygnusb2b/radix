<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubmissionController extends AbstractAppController
{
    public function inquiryAction(Request $request)
    {
        // Retrieve required services.
        $customerFactory    = $this->get('app_bundle.factory.customer.account');
        $identityFactory    = $this->get('app_bundle.factory.customer.identity');
        $customerManager    = $this->get('app_bundle.customer.manager');
        $submissionFactory  = $this->get('app_bundle.factory.input_submission');
        $serializer         = $this->get('app_bundle.serializer.public_api');

        // Create the payload instance: @todo, parameters should support dot notation and return arrays as parameter instances.
        $payload = RequestPayload::createFrom($request);

        // Validate... @todo Again, this should use a standard form handler to determine what is/isn't required.
        $this->validateInquiryPayload($payload);

        // Create the submission.
        $submission = $submissionFactory->create($payload);
        $submission->set('sourceKey', 'inquiry');

        if (null !== $customer = $customerManager->getActiveAccount()) {
            // A customer account is currently logged in.
            $isIdentity = false;

            // Set the customer factory.
            $customerFactory = $this->get('app_bundle.factory.customer.account');

            // Make sure email isn't updated by this form!
            $payload->getCustomer()->remove('primaryEmail');

            // Update customer data from this submission.
            $customerFactory->apply($customer, $payload->getCustomer()->all());

        } else {

             // A customer account is NOT logged in.
            $isIdentity = true;

            // Set the customer factory.
            $customerFactory = $this->get('app_bundle.factory.customer.identity');

            // Attempt to find the identity.
            $emailAddress = $payload->getCustomer()->get('primaryEmail');
            $customer = $this->get('as3_modlr.store')->findQuery('customer-identity', ['email' => $emailAddress])->getSingleResult();

            if (null === $customer) {
                // No identity found. Create.
                $customer = $customerFactory->create($payload->getCustomer()->all());
            } else {
                // Update the existing identity.
                $customerFactory->apply($customer, $payload->getCustomer()->all());
            }
        }

        // Set the account/identity to the submission.
        $submission->set('customer', $customer);

        // Throw error if unable to update the customer or create submission.
        if (true !== $result = $customerFactory->canSave($customer)) {
            $result->throwException();
        }
        if (true !== $result = $submissionFactory->canSave($submission)) {
            $result->throwException();
        }

        // Save everything
        $customerFactory->save($customer);
        $submissionFactory->save($submission);

        if (true === $isIdentity) {
            // Have to manually set new identity.
            $customerManager->setActiveIdentity($customer);
        }

        // @todo Send email notifications.

        // @todo The serialized customer and submission should be sent to the template for processing.
        return new JsonResponse(['data' => [
            // 'customer'   => $serializer->serialize($customer),
            // 'submission' => $serializer->serialize($submission),
            'template'   => '<h3>Thank you!</h3><p>Your submission has been received.</p>',
        ]], 201);
    }

    /**
     * @todo    This should move into the form/submission validation service.
     * @param   RequestPayload   $payload
     * @throws  HttpFriendlyException
     */
    private function validateInquiryPayload(RequestPayload $payload)
    {
        // @todo Validation rules would need to be handled by a form model.
        $meta = $payload->getMeta();
        if (false === $meta->has('model')) {
            throw new HttpFriendlyException('No meta.model member was found in the payload. Unable to process submission.', 422);
        }

        $model = $meta->get('model', []);
        if (!HelperUtility::isSetNotEmpty($model, 'type') || !HelperUtility::isSetNotEmpty($model, 'identifier')) {
            throw new HttpFriendlyException('The inquiry model type and identifier are required', 400);
        }

        $customerManager = $this->get('app_bundle.customer.manager');

        // Validation that should only run for non-logged in users.
        if (null !== $customer = $customerManager->getActiveAccount()) {
            if (null !== $customer->get('primaryEmail')) {
                return $payload;
            }
        }

        $email = ModelUtility::formatEmailAddress($payload->getCustomer()->get('primaryEmail'));
        if (empty($email)) {
            throw new HttpFriendlyException('The email address field is required.', 400);
        }
        if (false === ModelUtility::isEmailAddressValid($email)) {
            throw new HttpFriendlyException('The provided email address is invalid.', 400);
        }

        return $payload;
    }
}
