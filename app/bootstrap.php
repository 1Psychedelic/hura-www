<?php


use HuraTabory\Web\FrontController;

require __DIR__ . '/dev.php';

$root = __DIR__ . '/../';
require $root . 'vendor/autoload.php';

Tracy\Debugger::enable(
    DEV ? Tracy\Debugger::DEVELOPMENT :Tracy\Debugger::PRODUCTION
    //Tracy\Debugger::DEVELOPMENT
    //['127.0.0.1', 'my-tracy-secret@94.230.144.41']
    ,
    $root . 'log'
);
//Tracy\Debugger::$strictMode = TRUE;

$c = require 'container.php';

if(php_sapi_name() === 'cli') {

    //include 'cli.php';

} else {
    try {
        $c->get(FrontController::class)->run();
    } catch (Throwable $e) {
        if (DEV) {
            throw $e;
        }

        $c->get(\Hafo\Exceptionless\Client::class)->logException($e);
    }
}
