<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../vendor/mdbitz/hapi/HarvestAPI.php';
spl_autoload_register( array('HarvestAPI', 'autoload') );
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
