<?php

namespace AppBundle\Controller\App;

use \DateTime;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Serializer\PublicApiRules as Rules;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SubmissionController extends AbstractAppController
{
    public function inquiryAction(Request $request)
    {
        throw new HttpFriendlyException('Inquiry submission is not implemented... yet.', 501);

        // Parse the data into an object...

        // Store the raw submission data.

        // If customer logged in...
            // Update any customer data
            // Link the submission to the logged in account

        // Link to a customer-identity using the email address
            // If session cookie is present, also update the customer data
            // If not, simply link to the identity
            // If no identity found, create, set customer data, and link submission
            // Drop a cookie of some sort

        // Determine how to send notifications
        // Thank you to the customer
        // Notify sales contacts, etc, based on passed meta.

    }
}
