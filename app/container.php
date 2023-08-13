<?php
date_default_timezone_set('Europe/Prague');
setlocale(LC_ALL, 'cs_CZ.utf8');
header('X-Frame-Options: SAMEORIGIN');
header('Content-Type: text/html; charset=utf-8');
header('X-Powered-By: <3');

/** @var \Hafo\DI\ContainerBuilder $builder */
$builder = require 'config.php';
$c = $builder->createContainer();

Nette\Reflection\AnnotationsParser::setCacheStorage($c->get(Nette\Caching\IStorage::class));
Nette\Reflection\AnnotationsParser::$autoRefresh = DEV;

return $c;

