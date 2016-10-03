<?php

namespace AppBundle\Controller\App;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
}
