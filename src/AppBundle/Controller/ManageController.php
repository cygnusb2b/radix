<?php

namespace AppBundle\Controller;

use AppBundle\Utility\ModelUtility;
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
     */
    public function indexAction(Request $request)
    {
        $manager = $this->get('app_bundle.input.submission_manager');
        $payload = [
            'email-address'             => 'jacob.bare@gmail.com',
            'first-name'                => 'Jacob',
            'last-name'                 => 'Bare',
            '57d86d76d78c6af29f0041d2'  => 'Director, Analytics',
            'omeda-5070382'             => '57d6e6c4d78c6abd830041c1',
            'omeda-5070380'             => '5072061',
            'omeda-5070381'             => 'Maintenance Management',
            'question-not-found'        => 'foo',
            'omeda-5070383'             => 'Value not found!',
        ];

        $source = $this->get('as3_modlr.store')->findQuery('input-source', ['key' => 'request-more-information'])->getSingleResult();
        var_dump($source->get('key'));
        die();

        $manager->processSubmission($payload, $source, null, null);
        var_dump(__METHOD__);
        die();

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
        $prod = 'prod' === $this->get('kernel')->getEnvironment();
        return [
            'APP'                       => [
                'name'      => 'radix',
                'version'   => '0.0.0 a7b71d9c',
            ],
            'baseURL'                   => '/',
            'modulePrefix'              => 'radix',
            'locationType'              => 'auto',
            'environment'               => $prod ? 'production' : 'development',
            'LOG_TRANSITIONS'           => !$prod,
            'LOG_TRANSITIONS_INTERNAL'  => !$prod,
            'formAnswerTypes'           => ModelUtility::getFormAnswerTypes(true),
            'simpleScheduleTypes'       => ModelUtility::getSimpleScheduleTypes(true),
        ];
    }
}
