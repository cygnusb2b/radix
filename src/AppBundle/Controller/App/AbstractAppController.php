<?php

namespace AppBundle\Controller\App;

use AppBundle\Controller\AbstractController;
use AppBundle\Security\User\Customer;

abstract class AbstractAppController extends AbstractController
{
    /**
     * Gets the Customer security user instance, if available.
     *
     * @return  Customer|null
     */
    protected function getCustomer()
    {
        $user = $this->getUserToken()->getUser();
        return $user instanceof Customer ? $user : null;
    }
}
