<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\ModelUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthController extends AbstractAppController
{
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

    public function sendVerifyEmailAction(Request $request)
    {
        $emailAddress = ModelUtility::formatEmailAddress($request->query->get('email'));
        if (empty($emailAddress)) {
            throw new HttpFriendlyException('No email address was provided. Unable to send verification email.', 400);
        }
        // @todo this should use the method found in the customer email factory.
        $criteria = [
            'value'    => $emailAddress,
            'verification.verified' => true,
        ];
        $email = $this->get('store')->findQuery('customer-email', $criteria)->getSingleResult();
        if (null !== $email) {
            throw new HttpFriendlyException(sprintf('The email address "%s" is already verified.', $email->get('value')), 400);
        }

        // Need to determine which email address to send, if multiples.
        // Use a customer id, if provided, else use the most recently created email.
        $criteria = [
            'value'                 => $emailAddress,
            'verification.verified' => false,
        ];
        $customerId = $request->query->get('customer');
        if (HelperUtility::isMongoIdFormat($customerId)) {
            $criteria['customer'] = $customerId;
        }
        $email = $this->get('store')->findQuery('customer-email', $criteria, [], ['createdDate' => -1])->getSingleResult();
        if (null === $email) {
            throw new HttpFriendlyException(sprintf('No linked account was found for email address "%s"', $emailAddress), 404);
        }
        $token = $this->get('app_bundle.customer.email_verify.token_generator')->createFor($emailAddress, $email->get('account')->getId());
        $email->set('token', $token);
        $email->set('sentDate', new \DateTime());
        $email->set('completedDate', null);

        $email->save();
        // @todo Send the notification.

    }

    public function verifyEmailAction(Request $request)
    {
        $token = $request->query->get('token');
        if (empty($token)) {
            throw new HttpFriendlyException('No email verification token was provided. Unable to verify.', 400);
        }
        $email = $this->get('store')->findQuery('customer-email', ['verification.token' => $token])->getSingleResult();
        if (null === $email) {
            throw new HttpFriendlyException('No email address was found for the provided token.', 404);
        }
        if (true === $email->get('verification')->get('verified')) {
            throw new HttpFriendlyException(sprintf('The email address "%s" is already verified.', $email->get('value')), 400);
        }

        $generator = $this->get('app_bundle.customer.email_verify.token_generator');

        try {
            // @todo Need a way to provide the customer id for the verification re-send.
            $parsed = $generator->parseFor($token, $email->get('value'), $email->get('account')->getId());
        } catch (AuthenticationException $e) {
            throw new HttpFriendlyException('The provided token is either invalid or expired.', 403);
        }

        // Considered valid now.
        $email->set('verified', true);
        $email->set('completedDate', new \DateTime());

        // Are there any other actions that need to be taken? Delete unverified email addresses that match?
        $email->save();

        // An identity should now be linked to this customer.
        $identity = $this->get('store')->findQuery('customer-identity', ['email' => $email->get('value')])->getSingleResult();
        if (null !== $identity) {
            $identity->set('account', $email->get('account'));
            $identity->save();
        }

        // The customer should now be automatically logged in.


    }
}
