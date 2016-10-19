<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentityController extends AbstractAppController
{
    /**
     * Handles an identity detection request.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function detectAction(Request $request)
    {
        $manager = $this->get('app_bundle.customer.manager');
        return new JsonResponse(['data' => ['identity' => null]], 200);
        return $manager->createAuthResponse();
    }
}
