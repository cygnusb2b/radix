<?php

namespace AppBundle\Controller;

use AppBundle\Security\User\CoreUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{
    /**
     * Creates a new, core user.
     *
     */
    public function userCreateAction()
    {
        // @todo Implement. Must ensure the user has appropriate permission to create new users.
        throw new \BadMethodCallException('NYI');
    }

    /**
     * Retrieves the core user auth state.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function userRetrieveAction(Request $request)
    {
        $storage = $this->get('security.token_storage');
        $manager = $this->get('app_bundle.security.auth.generator_manager');
        return $manager->createResponseFor($storage->getToken()->getUser());
    }
}
