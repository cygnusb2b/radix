<?php

namespace AppBundle\Controller;

use AppBundle\Utility\ModelUtility;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ManageController extends AbstractController
{
    /**
     * Displays the modlr management application.
     *
     * @param   Request $request
     * @return  Response
     */
    public function indexAction(Request $request)
    {
        return $this->render('@AppBundle/Resources/views/radix.html.twig', [
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
        $types = [];
        $typeManager = $this->get('app_bundle.question.type_manager');
        foreach ($typeManager->getQuestionTypes() as $type) {
            $types[] = [
                'value' => $type->getKey(),
                'label' => $type->getDescription(),
            ];
        }

        $prod = 'prod' === $this->get('kernel')->getEnvironment();
        return [
            'APP'                       => [
                'name'      => 'radix',
                'version'   => '0.0.0 a7b71d9c',
            ],
            'baseURL'                   => '/manage',
            'rootURL'                   => '/',
            'modulePrefix'              => 'radix',
            'locationType'              => 'auto',
            'environment'               => $prod ? 'production' : 'development',
            'LOG_TRANSITIONS'           => !$prod,
            'LOG_TRANSITIONS_INTERNAL'  => !$prod,
            'formAnswerTypes'           => $types,
            'simpleScheduleTypes'       => ModelUtility::getSimpleScheduleTypes(true),
        ];
    }
}
