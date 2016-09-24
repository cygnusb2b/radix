<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SandboxController extends Controller
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
            'appId' => '97b09a4b-8eb8-475f-b72f-19d0f2073256',
            'host'  => 'dev.radix.vehicleservicepros.com',
            'debug' => true,
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
            $base    = sprintf('http://%s/lib/radix.%s?x-radix-appid=', $config['host'], $extension);
            $url     = sprintf('%s%s', $base, $config['appId']);
            $display = sprintf('%s{app-id}', $base);
            // $headers = @get_headers($url);
            // $found   = isset($headers[0]) && false !== stripos($headers[0], '200 OK');
            $libraries[$extension] = ['url' => $url, 'display' => $display];
        }
        return $libraries;
    }
}
