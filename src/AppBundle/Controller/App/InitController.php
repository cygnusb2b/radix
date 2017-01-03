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
        $this->appendSettings($app);
        $serialized = $this->get('app_bundle.serializer.public_api')->serialize($app);
        $caching    = $this->get('app_bundle.caching.response_cache');
        $response   = new JsonResponse($serialized);

        $caching->addStandardHeaders($response, $app->get('updatedDate'), 300);
        return $response;
    }

    /**
     * Ensures that the settings are pre-filled with defaults if empty.
     *
     * @param   Model   $app
     */
    private function appendSettings(Model $app)
    {
        $settings = $app->get('settings');
        if (null === $settings) {
            $settings = $app->createEmbedFor('settings');
            $app->set('settings', $settings);
        }
        foreach ($settings->getMetadata()->getEmbeds() as $key => $embedMeta) {
            if (null === $settings->get($key)) {
                $settings->set($key, $settings->createEmbedFor($key));
            }
        }
    }
}
