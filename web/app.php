<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('prod', false);

$env = getenv('SYMFONY_ENV');
$prod = $env === 'prod';
$debug = getenv('SYMFONY_DEBUG') !== '0' && !$prod;

if ($debug) {
    Debug::enable();
}

if ($prod) {
  // We don't want any cache in dev mode but in production, let's get it!
  $kernel->loadClassCache();
} else {
  // Will be either 'dev' or 'test'
  $kernel = new AppKernel($env, true);
}


$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
