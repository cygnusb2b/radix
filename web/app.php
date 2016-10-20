<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$env   = strtolower(getenv('APP_ENV'));
$debug = 'dev' === $env;

if ('prod' === $env) {
    $kernel = new AppKernel('prod', $debug);
    $kernel->loadClassCache();
} else {
    $kernel = new AppKernel('dev', $debug);
}

$request = Request::createFromGlobals();

// Set trusted proxies (varnish) based on server environment variable 'TRUSTED_PROXIES'
$proxies = [ '127.0.0.1', '192.168.0.1/22' ];
if (getenv('TRUSTED_PROXIES')) {
    $proxies = explode(',', getenv('TRUSTED_PROXIES'));
}
Request::setTrustedProxies($proxies);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
