<?php

namespace AppBundle\Controller;

use AppBundle\Core\AccountManager;
use AppBundle\Cors\CorsDefinition as Cors;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SandboxController extends AbstractController
{
    /**
     * @var null|array
     */
    private $apps;

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

        $app  = $this->getActiveApp($request);
        $apps = $this->getApplications($request->getSchemeAndHttpHost());

        $template = (empty($path)) ? 'index' : $path;
        return $this->render(sprintf('@AppBundle/Resources/views/sandbox/%s.html.twig', $template), [
            'app'        => $app,
            'apps'       => $apps,
            'libraries'  => $this->getLibraries($app),
            'navigation' => $this->buildNavigation($request),
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
            ['label' => 'Comments', 'path' => '/comments', 'children' => []],
            ['label' => 'Inquiry', 'path' => '/inquiry', 'children' => []],
            ['label' => 'Email Subs', 'path' => '/email-subscriptions', 'children' => []],
            ['label' => 'Gating', 'path' => '#', 'children' => [
                ['label' => 'Downloads', 'path' => '/gated-downloads'],
            ]],
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
     * Gets the active application.
     *
     * @param   Request $request
     * @return  array
     * @throws  \InvalidArgumentException
     */
    private function getActiveApp(Request $request)
    {
        $session = $request->getSession();
        $origin  = $request->getSchemeAndHttpHost();
        $apps    = $this->getApplications($origin);

        if (null !== $appId = $request->query->get('appId')) {
            if (!isset($apps[$appId])) {
                throw new \InvalidArgumentException('The request app is not supported by this origin.');
            }
            $session->set('activeSandboxApp', $apps[$appId]);
        }

        $app = $session->get('activeSandboxApp');
        if (!is_array($app) || !isset($apps[$app['appId']])) {
            $app = reset($apps);
            $session->set('activeSandboxApp', $app);
        }
        return $app;
    }

    /**
     * Gets the associated Radix libraries (js/css).
     *
     * @param   array   $app
     * @return  array
     */
    private function getLibraries(array $app)
    {
        $config = $app['config'];
        $libraries = [];
        foreach (['js', 'css'] as $extension) {
            $url = sprintf('%s/lib/radix.%s?x-radix-appid=%s', $config['host'], $extension, $config['appId']);
            $libraries[$extension] = ['url' => $url];
        }
        return $libraries;
    }

    /**
     * Loads all available applications based on the request origin.
     *
     * @param   string  $requestOrigin
     * @return  array
     * @throws  \RuntimeException
     */
    private function getApplications($requestOrigin)
    {
        if (null !== $this->apps) {
            return $this->apps;
        }
        $this->apps = [];
        foreach ($this->get('as3_modlr.store')->findAll('core-application') as $app) {
            $origins = array_merge(AccountManager::getGlobalOrigins(), $app->get('allowedOrigins'));
            foreach ($origins as $origin) {
                if (true === Cors::isOriginMatch($requestOrigin, $origin)) {
                    $key         = $app->get('publicKey');
                    $name        = $app->get('name');
                    $accountName = $app->get('account')->get('name');
                    $fullName    = sprintf('%s: %s', $accountName, $name);

                    $this->apps[$key] = [
                        'id'        => $app->getId(),
                        'fullName'  => $fullName,
                        'name'      => $name,
                        'account'   => $accountName,
                        'appId'     => $key,
                        'config'    => [
                            'appId'     => $key,
                            'host'      => $requestOrigin,
                            'debug'     => true,
                            'logLevel'  => 'log',
                        ],
                    ];
                    continue 2;
                }
            }
        }

        if (empty($this->apps)) {
            throw new \RuntimeException('Unable to load any applications for the provided request origin.');
        }

        uasort($this->apps, function($a, $b) {
            return $a['fullName'] < $b['fullName'] ? -1 : 1;
        });
        return $this->apps;
    }
}
