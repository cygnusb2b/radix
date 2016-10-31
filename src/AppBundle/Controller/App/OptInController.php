<?php

namespace AppBundle\Controller\App;

use AppBundle\Utility\ModelUtility;
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

        $emailAddress = ModelUtility::formatEmailAddress($emailAddress);

        $data = [];
        if (!empty($emailAddress)) {
            $collection = $store->findQuery('product-email-deployment-optin', ['email' => $emailAddress]);
            foreach ($collection as $model) {
                $data[$model->get('product')->getId()] = $model->get('optedIn');
            }
        }

        $data = empty($data) ? new \stdClass() : $data;
        return new JsonResponse(['data' => $data]);
    }
}
