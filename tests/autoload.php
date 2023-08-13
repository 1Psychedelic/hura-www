<?php

include_once __DIR__ . '/../vendor/autoload.php';

define('DEV', TRUE);

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4('Tests\\', __DIR__, TRUE);
$classLoader->register();
