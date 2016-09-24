<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractController extends Controller
{
    /**
     * Gets the current security user token.
     *
     * @return  \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    protected function getUserToken()
    {
        return $this->get('security.token_storage')->getToken();
    }
}
