<?php

namespace AppBundle\Controller\App;

use As3\Modlr\Models\Model;
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
    public function defaultAction(Request $request)
    {
        $manager    = $this->get('app_bundle.core.account_manager');
        $app        = $manager->getApplication();
        $serialized = $this->get('app_bundle.serializer.public_api')->serialize($app);
        $caching    = $this->get('app_bundle.caching.response_cache');
        $response   = new JsonResponse($serialized);

        $caching->addStandardHeaders($response, $app->get('updatedDate'), 300);
        return $response;
    }
}
