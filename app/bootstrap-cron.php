<?php


use Hafo\DI\Container;

require __DIR__ . '/dev.php';

$root = __DIR__ . '/../';
require $root . 'vendor/autoload.php';

/*Tracy\Debugger::enable(
    DEV ? Tracy\Debugger::DEVELOPMENT : Tracy\Debugger::PRODUCTION,
    $root . 'log'
);*/

/** @var Container $c */
$c = require 'container.php';

$c->get(\Hafo\Cron\CronRunner::class)->run($_GET['category'] ?? '');
