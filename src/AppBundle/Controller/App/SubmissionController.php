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

            // Make sure email isn't updated by this form!
            $payload->getCustomer()->remove('primaryEmail');

            // Update customer data from this submission.
            $customerFactory->apply($customer, $payload->getCustomer()->all());

            $submission->set('customer', $customer);

            // Throw error if unable to update the customer or create submission.
            if (true !== $result = $customerFactory->canSave($customer)) {
                $result->throwException();
            }
            if (true !== $result = $submissionFactory->canSave($submission)) {
                $result->throwException();
            }

            // Save everything
            foreach ($customerFactory->getRelatedModelsFor($customer) as $model) {
                $model->save();
            }

            foreach ($submissionFactory->getRelatedModelsFor($submission) as $model) {
                $model->save();
            }

            // @todo The serialized customer and submission should be sent to the template for processing.
            return new JsonResponse(['data' => [
                // 'customer'   => $serializer->serialize($customer),
                // 'submission' => $serializer->serialize($submission),
                'template'   => '<h3>Thank you!</h3><p>Your submission has been received.</p>',
            ]], 201);
        }
        throw new HttpFriendlyException('Submitting an inquiry while not logged in is not implemented yet.');

        // If customer logged in...
            // Update the root customer account data with the submission (not email address!!)
            // Link the submission to the customer account

        // Else customer not logged in...
            // Attempt to find customer identity... (do not need to check identity session cookie directly because this form has an email address)
                // Customer identity found in DB using the email address in the submission
                    // Link submission to the found identity
                    // Update the root identity data with the submission
                    // Drop identity cookies (session and visitor)

                // Customer identity not found in DB using the email address in the submission
                    // Create the identity based on the submission
                    // Link the submission to the new identity
                    // Drop identity cookies (session and visitor)

        // Once submission and customer dance complete...
        // Determine what notifications should be sent
            // Thank to the customer... (uses the inquiry thank you template)
            // Notify individuals based on provided notify settings (e.g. sales contact, etc)... (uses the inquiry notify template)

        // Determine what should now appear in the inquiry container... some sort of thank you, related products, etc... ??
        // Return response for React handling.
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

        $email = $payload->getCustomer()->get('primaryEmail');
        if (empty($email)) {
            throw new HttpFriendlyException('The email address field is required.', 400);
        }

        return $payload;
    }
}
