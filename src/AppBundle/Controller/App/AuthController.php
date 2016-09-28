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
        $identityFactory    = $this->get('app_bundle.factory.customer.identity');
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

        // Save everything
        $customerFactory->save($customer);
        $submissionFactory->save($submission);

        // @todo Create an identity if one doesn't exist. Find the identity if it does exist. Do NOT link the existing identity to the account until the email is verified.
        // Attempt to find the identity.
        $emailAddress = $payload->getCustomer()->get('primaryEmail');
        $identity = $this->get('as3_modlr.store')->findQuery('customer-identity', ['email' => $emailAddress])->getSingleResult();

        if (null === $identity) {
            // No identity found. Create.
            $identity = $identityFactory->create($payload->getCustomer()->all());
            if (true === $identityFactory->canSave($identity)) {
                $identityFactory->save($identity);
            }
        }

        // @todo Send email notifications.

        // @todo Set the identity cookies.

        // Send the create response.
        // @todo The serialized customer and submission should be sent to the template for processing.
        $contents = '
            <div class="card card-block">

              <h2 class="card-title">Thank you for signing up!</h2>

              <p class="alert alert-info" role="alert">Before you can log in, you must <strong>verify</strong> your email address.</p>

              <p class="card-text">Please check the inbox for <strong>' . $customer->get('primaryEmail') . '</strong> and click the link provided in the verification email.</p>
              <p class="card-text">The verification email was sent from <i>Sender Name Here <small>&lt;no-reply@domain.com&gt;</small></i> with a subject line of <i>Subject Line Here</i></p>
              <p class="card-text">If you\'re having trouble finding the email, you may resend the verification to your address or contact our support team.</p>
              <a href="#" class="btn btn-info">Resend Verification Email</a>
            </div>
        ';
        return new JsonResponse(['data' => [
            'template'  => $contents
        ]], 201);
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
