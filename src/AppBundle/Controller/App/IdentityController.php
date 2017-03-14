<?php

namespace AppBundle\Controller\App;

use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\RequestUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentityController extends AbstractAppController
{
    /**
     * Gets information on the currently discovered identity, if applicable.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function indexAction(Request $request)
    {
        $manager  = $this->get('app_bundle.identity.manager');
        $identity = $manager->getActiveIdentity();
        if (null === $identity) {
            return new JsonResponse(['data' => new \stdClass()]);
        }
        $serializer = $this->get('app_bundle.serializer.public_api');
        $serialized = $serializer->serialize($identity);
        return new JsonResponse($serialized);
    }

}
