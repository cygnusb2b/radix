<?php

namespace AppBundle\Controller\App;

use Symfony\Component\HttpFoundation\JsonResponse;

class OptInController extends AbstractAppController
{
    /**
     * Gets email deployment opt-in statuses for the provided email address.
     *
     * @param   string  $emailAddress
     * @return  JsonResponse
     */
    public function emailDeploymentAction($emailAddress)
    {
        $store      = $this->get('as3_modlr.store');
        $serializer = $this->get('app_bundle.serializer.public_api');
        $collection = $store->findQuery('product-email-deployment-optin', ['email' => $emailAddress]);
        return new JsonResponse(['data' => $serializer->serializeArray($collection->allWithoutLoad())]);
    }
}
