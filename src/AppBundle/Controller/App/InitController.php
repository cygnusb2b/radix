<?php

namespace AppBundle\Controller\App;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InitController extends AbstractAppController
{
    /**
     * Retrieves the application state.
     *
     * @return  JsonResponse
     */
    public function defaultAction()
    {
        $manager    = $this->get('app_bundle.core.account_manager');
        $serialized = $this->get('app_bundle.serializer.public_api')->serialize($manager->getApplication());
        return new JsonResponse($serialized);
    }
}
