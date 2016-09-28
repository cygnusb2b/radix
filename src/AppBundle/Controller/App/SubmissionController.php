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

        $store           = $this->get('as3_modlr.store');
        $factory         = $this->get('app_bundle.factory.customer_account');
        $customerManager = $this->get('app_bundle.customer.manager');
        $factory->setStore($store);

        // Create the payload instance: @todo, parameters should support dot notation and return arrays as parameter instances.
        $payload = RequestPayload::createFrom($request);

        // Validate... @todo Again, this should use a standard form handler to determine what is/isn't required.
        $this->validateInquiryPayload($payload);


        $submission = $store->create('input-submission');


        if (null !== $customer = $customerManager->getActiveAccount()) {
            // Make sure email isn't updated by this form!
            $payload->getCustomer()->remove('primaryEmail');

            // A customer account is currently logged in.
            $submission->set('customer', $customer);
            // Update customer data.
            $factory->apply($customer, $payload->getCustomer()->all());

            $factory->save($customer);


            // Save customer and submission.
        }

        var_dump(__METHOD__);

        die();

        // Services to use


        // Parse the data into an object...
        $payload = RequestUtility::extractPayload($request, false);






        // Create the submission

        $submission = $store->create('input-submission');

        // Needs to scope the payload. Eventually this should use the raw form submission (which should already be scoped).
        $submission->set('payload', $payload);
        $submission->set('sourceKey', 'inquiry');

        if (isset($data['submission']['referringUrl'])) {
            $submission->set('referringHost', $data['submission']['referringHost']);
            $submission->set('referringHref', $data['submission']['referringHref']);
        }

        if (null !== $customer = $customerManager->getActiveAccount()) {
            // A customer account is currently logged in.
            $submission->set('customer', $customer);
            // Update customer data.
            // Save customer and submission.
        }

        var_dump($customerManager->getActiveAccount());
        die();



        // Determine customer to use.






        // $submission->save();


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
        var_dump($payload['meta'], $data);
        die();
    }

    /**
     * @todo    This should move into the form/submission validation service.
     * @param   RequestPayload   $payload
     * @throws  HttpFriendlyException
     */
    private function validateInquiryPayload(RequestPayload $payload)
    {
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
