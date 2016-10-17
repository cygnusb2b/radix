<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SandboxController extends AbstractController
{
    /**
     * Displays sandbox pages. This is only available on dev environments.
     *
     * @param   string  $path
     * @param   Request $request
     * @return  Response
     */
    public function indexAction($path, Request $request)
    {
        if ('dev' !== $this->getParameter('kernel.environment')) {
            throw $this->createNotFoundException();
        }

        $template = (empty($path)) ? 'index' : $path;
        return $this->render(sprintf('@AppBundle/Resources/views/sandbox/%s.html.twig', $template), [
            'initConfig'    => $this->getInitConfig($request),
            'libraries'     => $this->getLibraries($request),
            'navigation'    => $this->buildNavigation($request),
        ]);
    }

    /**
     * Builds the top-level sandbox navigation
     *
     * @param   Request $request
     * @return  array
     */
    private function buildNavigation(Request $request)
    {
        $nav = [
            ['label' => 'Inquiry', 'path' => '/inquiry', 'children' => []],
            ['label' => 'Email Subs', 'path' => '/email-subscriptions', 'children' => []],
            ['label' => 'Action Handlers', 'path' => '/action-handlers', 'children' => []],
            ['label' => 'Utilities', 'path' => '#', 'children' => [
                ['label' => 'Query Parser', 'path' => '/query-parser'],
            ]],
        ];
        $path = $request->getPathInfo();
        foreach ($nav as &$item) {
            $item['active'] = ($item['path'] === $path) ? true : false;
            foreach ($item['children'] as &$child) {
                $child['active'] = ($child['path'] === $path) ? true : false;
                if ($child['active']) {
                    $item['active'] = true;
                }
            }
        }
        return $nav;
    }

    /**
     * Gets the library initialization config.
     *
     * @param   Request $request
     * @return  array
     */
    private function getInitConfig(Request $request)
    {
        $config = [
            'appId'    => '97b09a4b-8eb8-475f-b72f-19d0f2073256',
            'host'     => 'http://dev.radix.vehicleservicepros.com',
            'debug'    => true,
            'logLevel' => 'log'
        ];

        $query = $request->query->all();
        foreach ($config as $key => $value) {
            if (isset($query[$key])) {
                $config[$key] = $query[$key];
            }
        }
        return $config;
    }

    /**
     * Gets the associated Radix libraries (js/css).
     *
     * @param   Request $request
     * @return  array
     */
    private function getLibraries(Request $request)
    {
        $config = $this->getInitConfig($request);
        $libraries = [];
        foreach (['js', 'css'] as $extension) {
            $url = sprintf('%s/lib/radix.%s', $config['host'], $extension);
            $libraries[$extension] = ['url' => $url];
        }
        return $libraries;
    }
}
