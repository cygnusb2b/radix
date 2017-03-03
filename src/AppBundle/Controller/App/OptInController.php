<?php

namespace AppBundle\Controller\App;

use AppBundle\Utility\ModelUtility;
use Symfony\Component\HttpFoundation\JsonResponse;

class OptInController extends AbstractAppController
{
    /**
     * Gets email deployment opt-in statuses for the provided email address.
     *
     * @param   string|null  $emailAddress
     * @return  JsonResponse
     */
    public function emailDeploymentAction($emailAddress)
    {
        $data  = [];
        $store = $this->get('as3_modlr.store');

        $emailAddress = ModelUtility::formatEmailAddress($emailAddress);

        $optIns = [];

        if (!empty($emailAddress)) {
            $collection = $store->findQuery('product-email-deployment-optin', ['email' => $emailAddress], ['email' => 1, 'optedIn' => 1, 'product' => 1]);
            foreach ($collection as $optIn) {
                $productId = $optIn->get('product')->getId();
                $optIns[$productId] = $optIn->get('optedIn') ? 'true' : 'false';
            }
        }

        $collection = $store->findQuery('product', ['_type' => 'product-email-deployment'], ['_id' => 1, '_type' => 1]);
        foreach ($collection as $product) {
            $productId = $product->getId();
            $name = sprintf('submission:optIns.%s', $productId);
            $data[$name] = isset($optIns[$productId]) ? $optIns[$productId] : 'false';
        }

        $data = empty($data) ? new \stdClass() : $data;
        return new JsonResponse(['data' => $data]);
    }
}
