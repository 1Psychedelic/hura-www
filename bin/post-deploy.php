<?php

define('DEV', FALSE);

$root = __DIR__ . '/../';
require $root . 'vendor/autoload.php';

/** @var \Hafo\DI\Container $container */
$container = require __DIR__ . '/../app/container.php';

// post-deploy reminder
$todo = [
];

// remind
if(!empty($todo)) {
    echo "\n\nTODO:\n";
    echo implode("\n", array_map(function($val) {
        return '* ' . $val;
    }, $todo));
    echo "\n";
}
