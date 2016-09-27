<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\RequestUtility;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubmissionController extends AbstractAppController
{
    public function inquiryAction(Request $request)
    {

        var_dump($request->cookies->get('__olya'));
        die();
        // Parse the data into an object...
        $payload = RequestUtility::extractPayload($request, false);

        // Validate... @todo Again, this should use a standard form handler to determine what is/isn't required.
        $this->validateInquiryPayload($payload);

        // Format/validate email.
        $payload['data']['customer:primaryEmail'] = ModelUtility::formatEmailAddress($payload['data']['customer:primaryEmail']);

        $data = RequestUtility::parsePayloadData($payload['data']);

        // Determine customer to use.


        // Create the submission

        $submission = $store->create('input-submission');

        // Needs to scope the payload. Eventually this should use the raw form submission (which should already be scoped).
        $submission->set('payload', $payload);
        // $submission->set('customer', $customer);
        $submission->set('sourceKey', 'inquiry');

        if (isset($data['submission']['referringUrl'])) {
            $submission->set('referringHost', $data['submission']['referringHost']);
            $submission->set('referringHref', $data['submission']['referringHref']);
        }

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
     * @param   array   $payload
     * @throws  HttpFriendlyException
     */
    private function validateInquiryPayload(array $payload)
    {
        if (false === HelperUtility::isSetArray($payload, 'meta')) {
            throw new HttpFriendlyException('No meta member was found in the paylod. Unable to process submission.', 422);
        }

        if (false === HelperUtility::isSetArray($payload['meta'], 'model')) {
            throw new HttpFriendlyException('No meta.model member was found in the paylod. Unable to process submission.', 422);
        }

        $model = $payload['meta']['model'];
        if (!HelperUtility::isSetNotEmpty($model, 'type') || !HelperUtility::isSetNotEmpty($model, 'identifier')) {
            throw new HttpFriendlyException('The inquiry model type and identifier are required', 400);
        }

        if (false === HelperUtility::isSetNotEmpty($payload['data'], 'customer:primaryEmail')) {
            throw new HttpFriendlyException('The email address field is required.', 400);
        }
    }
}