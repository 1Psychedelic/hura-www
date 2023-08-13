<?php

define('DEV', FALSE);

$root = __DIR__ . '/../';
require $root . 'vendor/autoload.php';

/** @var \Hafo\DI\Container $container */
$container = require __DIR__ . '/../app/container.php';

// save current commit hash
$commit = '';
exec('git rev-parse HEAD', $commit);
$commit = $commit[0];
$commit = substr($commit, 0, 6);
file_put_contents($root . '/app/.hash', $commit);
