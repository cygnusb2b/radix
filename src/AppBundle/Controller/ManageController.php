<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageController extends Controller
{
    /**
     * Displays the modlr management application.
     *
     * @param   Request $request
     * @return  Response
     * @Route("/manage", name="manage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('@AppBundle/Resources/views/modlr.html.twig', [
            'emberConfig' => rawurlencode(json_encode($this->getEmberConfiguration()))
        ]);
    }

    /**
     * Gets the Ember application config.
     *
     * @return  array
     */
    private function getEmberConfiguration()
    {
        $prod = 'prod' === $this->get('kernel')->getEnvironment();
        return [
            'APP'                       => [
                'name'      => 'modlr',
                'version'   => '0.0.0 a7b71d9c',
            ],
            'modulePrefix'              => 'modlr',
            'environment'               =>  $prod ? 'production' : 'development',
            'LOG_TRANSITIONS'           =>  !$prod,
            'LOG_TRANSITIONS_INTERNAL'  =>  !$prod,
        ];
    }
}
