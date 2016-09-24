<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\ExceptionQueue;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Security\User\Customer;
use AppBundle\Utility\ModelCloner;
use AppBundle\Utility\RequestUtility;
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
        $payload = RequestUtility::extractPayload($request);

        // @todo Once form gen is in place, any front-end field validation (such as required, etc) should also be handled (again) on the backend
        // @todo This should be handled by a generic form validation service, that looks at the form in question, reads its validation rules, and validates the incoming data.
        // @todo Document how data flows through services, and at each point it's validated: controller -> validation service -> submit handler -> customer handler -> event subscriber
        // For now we'll handle manually in the controller.
        $this->validatePayload($payload);

        // At this point, the form is considered valid and can now be passed to the submission manager.
        // @todo The form config should determine which processor should run to handle the submission.
        // For now we'll process the submission manually, and then create the customer directly with the customer creator service.

        // Submit order: 1) customer-account 2) customer-email 3) input-submission

        // Services we'll be using...
        $provider       = $this->get('app_bundle.security.user_provider.customer');
        $store          = $this->get('as3_modlr.store');
        $encoder        = $this->get('security.password_encoder');


        // First, determine if a customer exists with these credentials.
        // @todo Add support for social!

        try {
            $email    = $payload['emails'][0]['value'];
            $customer = $provider->findViaPasswordCredentials($email);
            throw new HttpFriendlyException(sprintf('The email address "%s" is already associated with an account.', $email), 400);
        } catch (CustomUserMessageAuthenticationException $e) {
            // Pending email
            throw new HttpFriendlyException($e->getMessage(), 409);

        } catch (UsernameNotFoundException $e) {
            // Catch the not found exception so we can continue...
        }

        // @todo Have to account for when a user selects an email address that is in the system, but is unverified. This happens on verification, not customer create.
        // Good. No existing user found. We can create.
        $customer = $store->create('customer-account');

        // @todo Instead of using the array data directly, we should use a CustomerDefinition and CustomerEmailDefiniton object, similar to what was done with questions.
        // Apply fields, (manually for now).
        foreach ($customer->getMetadata()->getAttributes() as $key => $attrMeta) {
            if (isset($payload[$key])) {
                $customer->set($key, $payload[$key]);
            }
        }

        // @todo Apply demographic answers (manually for now).

        // Apply credentials (manually for now).
        $credentials = $customer->createEmbedFor('credentials');
        $password    = $credentials->createEmbedFor('password');

        // Could add salt here, if needed. But is recommended to NOT set a salt when using bcrypt.
        $password->set('salt', null);
        $credentials->set('password', $password);
        $customer->set('credentials', $credentials);

        // We're now good to encode/hash the password.
        $encoded = $encoder->encodePassword(new Customer($customer), $payload['credentials']['password']['value']);
        $password->set('value', $encoded);

        // Create the email model and assign the customer to it.
        $email = $store->create('customer-email');
        $email->set('value', $payload['emails'][0]['value']);
        $email->set('isPrimary', true);
        $email->set('account', $customer);

        // Create the initial email verification parameters.
        // Do not need to set the token here, as the internal subscriber will add a JWT.
        $verification = $email->createEmbedFor('verification');
        $verification->set('verified', false);
        $email->set('verification', $verification);

        // Save the customer and the email, in that order.
        $customer->save();
        $email->save();

        // Create the submission model at this point.
        $submission = $store->create('input-submission');

        // Erase credentials
        if (isset($payload['credentials'])) {
            unset($payload['credentials']);
        }
        // Needs to scope the payload. Eventually this should use the raw form submission (which should already be scoped).
        $scoped = [];
        foreach ($payload as $key => $value) {
            $scoped[sprintf('customer-account:%s', $key)] = $value;
        }
        $submission->set('payload', $scoped);
        $submission->set('customer', $customer);

        $submission->set('sourceKey', 'customer-account:create');
        $submission->save();



        // Now attempt to link an identity to the customer.
        // Identity linking does NOT happen here, it would happen once the user verifies their email address.
        $criteria = ['email' => $email->get('value')];
        $identity = $store->findQuery('customer-identity', $criteria)->getSingleResult();
        if (null !== $identity) {
            if (null !== $identity->get('account')) {
                throw new HttpFriendlyException('Unable to link identity. Account was created successfully.');
            }
            $identity->set('account', $customer);
            $identity->save();
        } else {
            // @todo This actually SHOULD create an identity (left unlinked), because user data was submitted. Its possible the user may never verify...
            // @todo On verification, check for this identity again and link
        }

        // @todo Now handle sending verification email

        // @todo Now insert the input-submission

        // Finally!! Send the create response. The front end will have to deal with notifying that the verification email was sent.
        return new JsonResponse(['data' => ['id' => $customer->getId(), 'email' => $email->get('value')]], 201);
    }

    /**
     * Retrieves the customer account's auth state.
     *
     * @return  JsonResponse
     */
    public function retrieveAction()
    {
        $token   = $this->getUserToken();
        $manager = $this->get('app_bundle.security.auth.generator_manager');
        return $manager->createResponseFor($token->getUser());
    }

    /**
     * @todo    This should move into the form/submission validation service.
     * @param   array   $payload
     * @throws  ExceptionQueue
     */
    private function validatePayload(array $payload)
    {
        $queue = new ExceptionQueue();

        // @todo Will need to be able to access arrays with dot notation.
        if (!isset($payload['emails'][0]['value']) || empty($payload['emails'][0]['value'])) {
            $queue->add(new HttpFriendlyException('The email address field is required.', 400));
        }

        if (!isset($payload['credentials']['password']['value']) || empty($payload['credentials']['password']['value'])) {
            $queue->add(new HttpFriendlyException('The password field is required.', 400));
        }

        if (false === $queue->isEmpty()) {
            throw $queue;
        }
    }
}
