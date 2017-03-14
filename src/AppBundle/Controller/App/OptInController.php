<?php

namespace AppBundle\Controller\App;

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
        $values = $this->loadOptInValues($emailAddress);
        $data = empty($values) ? new \stdClass() : $values;
        return new JsonResponse(['data' => $data]);
    }
}
