<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\ExceptionQueue;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Security\User\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthController extends Controller
{

    /**
     * Creates a new customer account.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function createAction(Request $request)
    {
        $payload = $this->extractPayload($request);

        // @todo Once form gen is in place, any front-end field validation (such as required, etc) should also be handled (again) on the backend
        // @todo This should be handled by a generic form validation service, that looks at the form in question, reads its validation rules, and validates the incoming data.
        // @todo Document how data flows through services, and at each point it's validated: controller -> validation service -> submit handler -> customer handler -> event subscriber
        // For now we'll handle manually in the controller.
        $this->validatePayload($payload);

        // At this point, the form is considered valid and can now be passed to the submission manager.
        // @todo The form config should determine which processor should run to handle the submission.
        // For now we'll process the submission manually, and then create the customer directly with the customer creator service.

        throw new HttpFriendlyException('Creating user accounts is not fully implemented, yet...', 501);

        // Log the customer in.
        $this->get('security.context')->setToken($token);
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        return $this->retrieveAction();
    }

    /**
     * Retrieves the customer account's auth state.
     *
     * @return  JsonResponse
     */
    public function retrieveAction()
    {
        $storage = $this->get('security.token_storage');
        $manager = $this->get('app_bundle.security.auth.generator_manager');
        return $manager->createResponseFor($storage->getToken()->getUser());
    }

    /**
     * Extracts the customer creation payload from the request.
     *
     * @todo    A generic form handling service should extract all form payloads.
     * @param   Request $request
     * @return  array
     * @throws  \InvalidArgumentException
     */
    private function extractPayload(Request $request)
    {
        if (0 !== stripos($request->headers->get('content-type'), 'application/json')) {
            throw new HttpFriendlyException('Invalid request content type. Expected application/json.', 415);
        }
        // JSON request.
        $payload = @json_decode($request->getContent(), true);
        if (!isset($payload['data'])) {
            throw new HttpFriendlyException('No data member was found in the request payload.', 422);
        }
        return (array) $payload['data'];
    }

    /**
     * @todo    This should move into the form/submission validation service.
     * @param   array   $payload
     * @throws  ExceptionQueue
     */
    private function validatePayload(array $payload)
    {
        $queue = new ExceptionQueue();

        $email   = null;
        $confirm = null;

        // @todo Will need to be able to access arrays with dot notation.
        if (!isset($payload['emails'][0]['value']) || empty($payload['emails'][0]['value'])) {
            $queue->add(new HttpFriendlyException('The email address field is required.', 400));
        } else {
            $email = $payload['emails'][0]['value'];
        }
        if (!isset($payload['emails'][0]['confirm']) || empty($payload['emails'][0]['confirm'])) {
            $queue->add(new HttpFriendlyException('The email address confirmation field is required.', 400));
        } else {
            $confirm = $payload['emails'][0]['confirm'];
        }

        if ($email !== $confirm) {
            $queue->add(new HttpFriendlyException('The email address must match the email confirmation field.', 400));
        }

        if (false === $queue->isEmpty()) {
            throw $queue;
        }
    }
}
