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
    public function configAction(Request $request)
    {
        $request->getSession()->start();
        $config = $this->getEmberConfiguration();
        $contents = rawurlencode(json_encode($config));
        return new Response(str_replace('__CONFIG__', $contents, "(function(document) {
                var meta = document.createElement('meta');
                meta.setAttribute('name', 'app/config/environment')
                meta.setAttribute('content', '__CONFIG__');
                document.getElementsByTagName('head')[0].appendChild(meta);
            })(document);
            "));
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
            'baseURL'                   => '/',
            'rootURL'                   => '/manage/',
            'modulePrefix'              => 'radix',
            'locationType'              => 'auto',
            'environment'               => $prod ? 'production' : 'development',
            'LOG_TRANSITIONS'           => !$prod,
            'LOG_TRANSITIONS_INTERNAL'  => !$prod,
            'formAnswerTypes'           => $types,
            'simpleScheduleTypes'       => ModelUtility::getSimpleScheduleTypes(true),
            'formKeys'                  => [
                ['value' => '', 'label' => ''],
                ['value' => 'Inquiry', 'label' => 'Inquiry'],
                ['value' => 'Register', 'label' => 'Register'],
                ['value' => 'Gated Download', 'label' => 'Gated Download'],
                ['value' => 'Email Subscriptions', 'label' => 'Email Subscriptions'],
            ],
        ];
    }
}
