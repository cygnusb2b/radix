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

// Load AppCache based on server environment variable 'APP_CACHE'
if (getenv('USE_CACHE') === 'true') {
    $kernel = new AppCache($kernel);
    // When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
    Request::enableHttpMethodParameterOverride();
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
