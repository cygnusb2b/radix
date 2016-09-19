<?php

namespace AppBundle\Controller\App;

use AppBundle\Security\User\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    /**
     * Creates a new customer account.
     *
     */
    public function createAction()
    {
        // @todo Implement. Must ensure the user has appropriate permission to create new users.
        throw new \BadMethodCallException('NYI');
    }

    /**
     * Retrieves the customer account's auth state.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function retrieveAction(Request $request)
    {
        $storage = $this->get('security.token_storage');
        $manager = $this->get('app_bundle.security.auth.generator_manager');
        $token   = $storage->getToken();

        if ($token->getUser() instanceof Customer)  {
            $payload = $manager->generateFor($token->getUser());
        } else {
            $payload = new \stdClass();
        }
        return new JsonResponse($payload);
    }

    /**
     * Submits customer account credentials (logs a customer in).
     * Is a placeholder for the firewall.
     */
    public function submitAction()
    {
    }
}
