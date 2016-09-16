<?php

namespace AppBundle\Controller;

use AppBundle\Security\User\CoreUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{
    public function userRetrieveAction(Request $request)
    {
        $storage = $this->get('security.token_storage');
        $manager = $this->get('app_bundle.security.auth.generator_manager');
        $token   = $storage->getToken();

        if ($token->getUser() instanceof CoreUser)  {
            $payload = $manager->generateFor($token->getUser());
        } else {
            $payload = new \stdClass();
        }
        return new JsonResponse($payload);
    }

    public function userSubmitAction()
    {
    }
}
