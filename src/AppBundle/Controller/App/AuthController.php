<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\ExceptionQueue;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Security\User\Customer;
use AppBundle\Utility\ModelCloner;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\ModelUtility;
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
        $payload = RequestUtility::extractPayload($request, false);
        $data    = RequestUtility::parsePayloadData($payload['data']);

        $store   = $this->get('as3_modlr.store');
        $factory = $this->get('app_bundle.factory.customer_account');
        $factory->setStore($store);

        $customerData = $data['customer'];

        $customer = $factory->create($customerData);

        if (true === $factory->canSave($customer)) {
            $toSave = $factory->getRelatedModelsFor($customer);
            foreach ($toSave as $model) {
                $model->save();

            }
        }

        // $factory->preValidate($customer);





        var_dump(__METHOD__);
        die();

        // @todo Once form gen is in place, any front-end field validation (such as required, etc) should also be handled (again) on the backend
        // @todo This should be handled by a generic form validation service, that looks at the form in question, reads its validation rules, and validates the incoming data.
        // @todo Document how data flows through services, and at each point it's validated: controller -> validation service -> submit handler -> customer handler -> event subscriber
        // For now we'll handle manually in the controller.


        // Format/validate email.


        // At this point, the form is considered valid and can now be passed to the submission manager.
        // @todo The form config should determine which processor should run to handle the submission.
        // For now we'll process the submission manually, and then create the customer directly with the customer creator service.

        // Submit order: 1) customer-account 2) customer-email 3) input-submission



        // Services we'll be using...
        $provider       = $this->get('app_bundle.security.user_provider.customer');
        $store          = $this->get('as3_modlr.store');

        $cloner         = $this->get('app_bundle.cloning.model_cloner');
        $answerFactory  = $this->get('app_bundle.question.answer_factory');


        // First, determine if a customer exists with these credentials.
        // @todo Add support for social!

        $customerData = $data['customer'];
        try {
            $email    = $customerData['primaryEmail'];
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
            if (isset($customerData[$key])) {
                $customer->set($key, $customerData[$key]);
            }
        }

        // Apply address data.
        $address = null;
        if (true === HelperUtility::isSetArray($customerData, 'primaryAddress')) {
            $address = $store->create('customer-address');
            $address->set('customer', $customer);
            foreach ($address->getMetadata()->getAttributes() as $key => $attrMeta) {
                if (isset($customerData['primaryAddress'][$key])) {
                    $address->set($key, $customerData['primaryAddress'][$key]);
                }
            }
        }

        // Apply answers
        $answers = [];
        if (true === HelperUtility::isSetArray($customerData, 'answers')) {
            $questionIds = [];
            foreach ($customerData['answers'] as $questionId => $answerId) {
                if (!HelperUtility::isMongoIdFormat($questionId)) {
                    continue;
                }
                $questionIds[] = $questionId;
            }
            $criteria = ['id' => ['$in' => $questionIds]];
            $questions = $store->findQuery('question', $criteria);
            foreach ($questions as $question) {
                if (true === $question->get('deleted')) {
                    continue;
                }
                $answerValue = $customerData['answers'][$question->getId()];

                $answer = $answerFactory->createAnswerFor('customer', $question, $answerValue);
                if (null === $answer) {
                    continue;
                }
                $answer->set('customer', $customer);
                $answers[] = $answer;
            }
        }

        // Apply credentials (manually for now).
        $credentials = $customer->createEmbedFor('credentials');
        $password    = $credentials->createEmbedFor('password');

        // Could add salt here, if needed. But is recommended to NOT set a salt when using bcrypt.
        $password->set('salt', null);
        $credentials->set('password', $password);
        $customer->set('credentials', $credentials);

        // We're now good to encode/hash the password.
        $encoded = $encoder->encodePassword(new Customer($customer), $customerData['password']);
        $password->set('value', $encoded);

        // Create the email model and assign the customer to it.
        $email = $store->create('customer-email');
        $email->set('value', $customerData['primaryEmail']);
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
        if (null !== $address) {
            $address->save();
        }
        foreach ($answers as $answer) {
            $answer->save();
        }

        // Create the submission model at this point.
        $submission = $store->create('input-submission');

        // Erase credentials
        if (isset($payload['data']['customer']['password'])) {
            unset($payload['data']['customer']['password']);
        }
        // Needs to scope the payload. Eventually this should use the raw form submission (which should already be scoped).
        $submission->set('payload', ['data' => $data]);
        $submission->set('customer', $customer);
        $submission->set('sourceKey', 'customer-account:create');

        if (isset($data['submission']['referringUrl'])) {
            $submission->set('referringHost', $data['submission']['referringHost']);
            $submission->set('referringHref', $data['submission']['referringHref']);
        }

        $submission->save();

        // Clone the answers.
        foreach ($answers as $answer) {
            $type = str_replace('customer-', 'input-', $answer->getType());
            $inputAnswer = $store->create($type);
            $cloner->apply($answer, $inputAnswer);
            $inputAnswer->set('submission', $submission);
            $inputAnswer->set('question', $answer->get('question'));
            $inputAnswer->save();
        }


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
        return new JsonResponse(['data' => ['id' => $customer->getId(), 'submission' => $submission->getId(), 'email' => $email->get('value')]], 201);
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
