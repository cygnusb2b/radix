<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\ExceptionQueue;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Security\User\Customer;
use AppBundle\Utility\ModelCloner;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
use AppBundle\Utility\RequestPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthController extends AbstractAppController
{
    /**
     * Creates a new customer account.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function createAction(Request $request)
    {
        // @todo Once form gen is in place, any front-end field validation (such as required, etc) should also be handled (again) on the backend
        // @todo This should be handled by a generic form validation service, that looks at the form in question, reads its validation rules, and validates the incoming data.
        // @todo Document how data flows through services, and at each point it's validated: controller -> validation service -> submit handler -> customer handler -> event subscribe

        // Retrieve required services.
        $customerFactory    = $this->get('app_bundle.factory.customer.account');
        $customerManager    = $this->get('app_bundle.customer.manager');
        $submissionFactory  = $this->get('app_bundle.factory.input_submission');

        if (null !== $customer = $customerManager->getActiveAccount()) {
            // Disallow creation while logged in.
            throw new HttpFriendlyException('A customer account is already logged in. Account creation is not available while logged in.', 400);
        }

        // Create the payload instance: @todo, parameters should support dot notation and return arrays as parameter instances.
        $payload = RequestPayload::createFrom($request);

        // Create the customer.
        // @todo Add support for social credentials.
        $customer = $customerFactory->create($payload->getCustomer()->all());

        // Create the submission.
        $submission = $submissionFactory->create($payload);
        $submission->set('sourceKey', 'customer-account');
        $submission->set('customer', $customer);

        // Throw error if unable to create the customer or submission.
        if (true !== $result = $customerFactory->canSave($customer)) {
            $result->throwException();
        }
        if (true !== $result = $submissionFactory->canSave($submission)) {
            $result->throwException();
        }

        // @todo Create an identity if one doesn't exist. Find the identity if it does exist. Do NOT link the existing identity to the account until the email is verified.

        // Save everything
        foreach ($customerFactory->getRelatedModelsFor($customer) as $model) {
            $model->save();
        }

        foreach ($submissionFactory->getRelatedModelsFor($submission) as $model) {
            $model->save();
        }

        // @todo Send email notifications.

        // @todo Set the identity cookies.

        // Send the create response. The front end will have to deal with notifying that the verification email was sent.
        return new JsonResponse(['data' => ['id' => $customer->getId(), 'submission' => $submission->getId(), 'email' => $customer->get('primaryEmail')]], 201);
    }

    /**
     * Retrieves the customer account's auth state.
     *
     * @return  JsonResponse
     */
    public function retrieveAction()
    {
        $manager = $this->get('app_bundle.customer.manager');
        return $manager->createAuthResponse();
    }
}
