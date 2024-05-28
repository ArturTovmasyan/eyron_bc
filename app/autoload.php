<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

// add new extension
$classLoader = new \Doctrine\Common\ClassLoader('DoctrineExtensions', __DIR__.'/../vendor/beberlei-doctrine-extensions/lib/DoctrineExtensions/');
$classLoader->register();

return $loader;
