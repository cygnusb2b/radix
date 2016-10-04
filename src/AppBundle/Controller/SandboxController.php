<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class SandboxController extends AbstractController
{
    public function indexAction(Request $request)
    {
        if ('dev' !== $this->getParameter('kernel.environment')) {
            throw $this->createNotFoundException();
        }

        return $this->render('@AppBundle/Resources/views/sandbox/index.html.twig', [
            'initConfig' => $this->getInitConfig($request),
            'libraries'   => $this->getLibraries($request),
        ]);
    }

    private function getInitConfig(Request $request)
    {
        $config = [
            'appId'  => '97b09a4b-8eb8-475f-b72f-19d0f2073256',
            'host'   => 'http://dev.radix.vehicleservicepros.com',
            'debug'  => true,
        ];

        $query = $request->query->all();
        foreach ($config as $key => $value) {
            if (isset($query[$key])) {
                $config[$key] = $query[$key];
            }
        }
        return $config;
    }

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
